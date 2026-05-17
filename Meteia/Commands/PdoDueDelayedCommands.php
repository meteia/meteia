<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Aura\Sql\ExtendedPdoInterface;
use DateInterval;
use DateTimeImmutable;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\Time\Clock;
use Override;
use Psr\Log\LoggerInterface;
use stdClass;
use Throwable;
use UnexpectedValueException;

final readonly class PdoDueDelayedCommands implements DueDelayedCommands
{
    private const int STALE_CLAIM_SECONDS = 300;

    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
        private CommandDeliveries $deliveries,
        private Clock $clock,
        private LoggerInterface $log,
    ) {}

    #[Override]
    public function dispatch(int $limit): int
    {
        $claimId = bin2hex(random_bytes(16));
        $this->claimDueCommands($claimId, max(1, $limit));

        $dispatched = 0;
        foreach ($this->claimedCommands($claimId) as $delayed) {
            $commandId = $delayed->commandId();
            try {
                $delivery = new CommandDelivery($commandId, $delayed->command(), $delayed->scope());
                $this->deliveries->publishDelivery($delivery);
                $this->markPublished($commandId);
                ++$dispatched;
            } catch (Throwable $throwable) {
                $this->markFailed($commandId, $throwable);
                $this->log->error($throwable->getMessage(), [
                    'commandId' => (string) $commandId,
                    'commandType' => $delayed->commandType(),
                ]);
            }
        }

        return $dispatched;
    }

    private function claimDueCommands(string $claimId, int $limit): void
    {
        $now = $this->clock->now();
        $staleBefore = $now->sub(new DateInterval('PT' . self::STALE_CLAIM_SECONDS . 'S'));

        foreach ($this->claimableCommandIds($now, $staleBefore, $limit) as $commandId) {
            $this->db->fetchAffected('
                UPDATE delayed_commands
                SET claimed_at = :claimedAt, claim_id = :claimId
                WHERE command_id = :commandId
                  AND published_at IS NULL
                  AND defer_until <= :now
                  AND (claimed_at IS NULL OR claimed_at < :staleBefore)
            ', [
                'claimedAt' => $this->format($now),
                'claimId' => $claimId,
                'commandId' => $commandId,
                'now' => $this->format($now),
                'staleBefore' => $this->format($staleBefore),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function claimableCommandIds(DateTimeImmutable $now, DateTimeImmutable $staleBefore, int $limit): array
    {
        $rows = $this->db->fetchObjects('
            SELECT command_id
            FROM delayed_commands
            WHERE published_at IS NULL
              AND defer_until <= :now
              AND (claimed_at IS NULL OR claimed_at < :staleBefore)
            ORDER BY defer_until ASC, command_id ASC
            LIMIT ' . $limit . '
        ', [
            'now' => $this->format($now),
            'staleBefore' => $this->format($staleBefore),
        ]);

        $commandIds = [];
        // @mago-expect analysis:mixed-assignment -- Aura returns database rows as mixed values; rowObject narrows each row.
        foreach ($rows as $row) {
            $commandIds[] = $this->binaryColumn($this->rowObject($row));
        }

        return $commandIds;
    }

    /**
     * @return list<PdoDelayedCommand>
     */
    private function claimedCommands(string $claimId): array
    {
        $rows = $this->db->fetchObjects('
            SELECT command_id, command_type, command, causation_id, correlation_id, process_id
            FROM delayed_commands
            WHERE claim_id = :claimId
              AND published_at IS NULL
            ORDER BY defer_until ASC, command_id ASC
        ', ['claimId' => $claimId]);

        $commands = [];
        // @mago-expect analysis:mixed-assignment -- Aura returns database rows as mixed values; rowObject narrows each row.
        foreach ($rows as $row) {
            $commands[] = new PdoDelayedCommand($this->rowObject($row), $this->messageSerializer);
        }

        return $commands;
    }

    private function markPublished(CommandId $commandId): void
    {
        $this->db->fetchAffected('
            UPDATE delayed_commands
            SET published_at = :publishedAt
            WHERE command_id = :commandId
        ', [
            'publishedAt' => $this->format($this->clock->now()),
            'commandId' => $commandId->bytes(),
        ]);
    }

    private function markFailed(CommandId $commandId, Throwable $throwable): void
    {
        $this->db->fetchAffected('
            UPDATE delayed_commands
            SET failed_at = :failedAt, failure = :failure
            WHERE command_id = :commandId
        ', [
            'failedAt' => $this->format($this->clock->now()),
            'failure' => substr($throwable->getMessage(), 0, 1024),
            'commandId' => $commandId->bytes(),
        ]);
    }

    private function format(DateTimeImmutable $instant): string
    {
        return $instant->format('Y-m-d H:i:s.u');
    }

    private function binaryColumn(stdClass $row): string
    {
        // @mago-expect analysis:mixed-assignment -- PDO rows expose database columns dynamically; this method narrows the value.
        $value = $row->command_id ?? null;
        if (!\is_string($value)) {
            throw new UnexpectedValueException('Delayed command column must be binary string: command_id');
        }

        return $value;
    }

    private function rowObject(mixed $row): stdClass
    {
        if (!$row instanceof stdClass) {
            throw new UnexpectedValueException('Delayed command query must return object rows');
        }

        return $row;
    }
}
