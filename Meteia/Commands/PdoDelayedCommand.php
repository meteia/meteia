<?php

declare(strict_types=1);

namespace Meteia\Commands;

use Meteia\MessageStreams\MessageSerializer;
use Meteia\ValueObjects\Identity\CausationId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\MessageScope;
use Meteia\ValueObjects\Identity\ProcessId;
use stdClass;
use UnexpectedValueException;

final readonly class PdoDelayedCommand
{
    public function __construct(
        private stdClass $row,
        private MessageSerializer $messageSerializer,
    ) {}

    public function commandId(): CommandId
    {
        return new CommandId($this->stringValue($this->row->command_id ?? null, 'command_id'));
    }

    public function commandType(): string
    {
        return $this->stringValue($this->row->command_type ?? null, 'command_type');
    }

    public function command(): Command
    {
        // @mago-expect analysis:mixed-assignment -- The serializer boundary returns mixed; the command interface narrows it below.
        $command = $this->messageSerializer->unserialize($this->stringValue($this->row->command ?? null, 'command'));
        if (!$command instanceof Command) {
            throw new UnexpectedValueException('Delayed command payload must unserialize to a command');
        }

        return $command;
    }

    public function scope(): MessageScope
    {
        return new MessageScope(
            new CorrelationId($this->stringValue($this->row->correlation_id ?? null, 'correlation_id')),
            new CausationId($this->stringValue($this->row->causation_id ?? null, 'causation_id')),
            new ProcessId($this->stringValue($this->row->process_id ?? null, 'process_id')),
        );
    }

    private function stringValue(mixed $value, string $column): string
    {
        if (!\is_string($value)) {
            throw new UnexpectedValueException('Delayed command column must be string: ' . $column);
        }

        return $value;
    }
}
