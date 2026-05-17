<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\MessageStreams\MessageSerializer;
use Meteia\ValueObjects\Identity\MessageScopeSource;
use Override;

final readonly class PdoDelayedCommandOutbox implements DelayedCommandOutbox
{
    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
        private MessageScopeSource $scopeSource,
    ) {}

    #[Override]
    public function publishAt(Command $command, DateTimeImmutable $when): void
    {
        $commandId = CommandId::random();
        $scope = $this->scopeSource->current();
        $this->db->fetchAffected('
            INSERT INTO delayed_commands (
                command_id, command_type, command,
                causation_id, correlation_id, process_id, defer_until
            )
            VALUES (
                :commandId, :commandType, :command,
                :causationId, :correlationId, :processId, :deferUntil
            )
        ', [
            'commandId' => $commandId->bytes(),
            'commandType' => $command::class,
            'command' => $this->messageSerializer->serialize($command),
            'causationId' => $scope->causationId()->bytes(),
            'correlationId' => $scope->correlationId()->bytes(),
            'processId' => $scope->processId()->bytes(),
            'deferUntil' => $when->format('Y-m-d H:i:s.u'),
        ]);
    }
}
