<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Aura\Sql\ExtendedPdoInterface;
use Meteia\Commands\Command;
use Meteia\Commands\CommandId;
use Meteia\Domain\Contracts\IssuedCommands;
use Meteia\MessageStreams\MessageSerializer;
use Override;

final readonly class PdoIssuedCommands implements IssuedCommands
{
    public function __construct(
        private ExtendedPdoInterface $db,
        private MessageSerializer $messageSerializer,
    ) {}

    #[Override]
    public function pending(): CommandMessages
    {
        // Reading pending issued commands back into typed `CommandMessage` instances requires
        // knowing the concrete `AggregateRootId` subtype per row, which the schema currently
        // does not capture. Wire up a dispatcher in a later phase that consumes the table directly.
        return new CommandMessages();
    }

    #[Override]
    public function append(CommandMetadata $metadata, Command $command): void
    {
        $commandId = CommandId::random();
        $this->db->fetchAffected('
            INSERT INTO issued_commands (
                command_id, aggregate_root_id, command_type, command,
                causation_id, correlation_id, issued_at, defer_until
            )
            VALUES (
                :commandId, :aggregateRootId, :commandType, :command,
                :causationId, :correlationId, :issuedAt, :deferUntil
            )
        ', [
            'commandId' => $commandId->bytes(),
            'aggregateRootId' => $metadata->aggregateRootId->bytes(),
            'commandType' => $command::class,
            'command' => $this->messageSerializer->serialize($command),
            'causationId' => $metadata->causationId->bytes(),
            'correlationId' => $metadata->correlationId->bytes(),
            'issuedAt' => $metadata->issuedAt->format('Y-m-d H:i:s.u'),
            'deferUntil' => $metadata->issuedAt->format('Y-m-d H:i:s.u'),
        ]);
    }
}
