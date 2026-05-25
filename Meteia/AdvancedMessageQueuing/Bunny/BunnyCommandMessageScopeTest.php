<?php

declare(strict_types=1);

namespace Meteia\AdvancedMessageQueuing\Bunny;

use Bunny\Message;
use Meteia\Commands\CommandId;
use Meteia\ValueObjects\Identity\CorrelationId;
use Meteia\ValueObjects\Identity\ProcessId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BunnyCommandMessageScopeTest extends TestCase
{
    public function testUsesCanonicalCommandMessageHeaders(): void
    {
        $commandId = CommandId::random();
        $correlationId = CorrelationId::random();
        $processId = ProcessId::random();

        $scope = new BunnyCommandMessageScope(new Message(null, null, null, '', '', [
            'message-id' => $commandId->token(),
            'correlation-id' => $correlationId->token(),
            'process-id' => $processId->token(),
        ], '{}'));

        static::assertTrue($scope->commandId()->equalTo($commandId), 'The command id should come from the canonical message-id header.');
        static::assertTrue($scope->scope()->correlationId()->equalTo($correlationId), 'The correlation id should come from the canonical correlation-id header.');
        static::assertTrue($scope->scope()->processId()->equalTo($processId), 'The process id should come from the canonical process-id header.');
    }

    public function testUsesStompCommandMessageHeaderAliases(): void
    {
        $commandId = CommandId::random();
        $correlationId = CorrelationId::random();
        $processId = ProcessId::random();

        $scope = new BunnyCommandMessageScope(new Message(null, null, null, '', '', [
            'message-id' => 'T_meteia-live-view@@session@@1',
            'x-meteia-command-id' => $commandId->token(),
            'correlation-id' => 'not-a-correlation-id',
            'x-meteia-correlation-id' => $correlationId->token(),
            'process-id' => 'not-a-process-id',
            'x-meteia-process-id' => $processId->token(),
        ], '{}'));

        static::assertTrue($scope->commandId()->equalTo($commandId), 'The command id should come from the STOMP command id alias.');
        static::assertTrue($scope->scope()->correlationId()->equalTo($correlationId), 'The correlation id should come from the STOMP correlation id alias.');
        static::assertTrue($scope->scope()->processId()->equalTo($processId), 'The process id should come from the STOMP process id alias.');
    }

    public function testMintsMissingOrMalformedCommandMessageHeaders(): void
    {
        $scope = new BunnyCommandMessageScope(new Message(null, null, null, '', '', [
            'message-id' => 'not-a-command-id',
            'correlation-id' => 'not-a-correlation-id',
            'process-id' => 'not-a-process-id',
        ], '{}'));

        static::assertSame(
            $scope->commandId()->hex(),
            $scope->scope()->causationId()->hex(),
            'The minted causation id should be derived from the minted command id.',
        );
    }
}
