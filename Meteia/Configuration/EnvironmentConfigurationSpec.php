<?php

declare(strict_types=1);

namespace Meteia\Configuration;

use Meteia\Configuration\Errors\UnexpectedType;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \Meteia\Configuration\EnvironmentConfiguration
 */
class EnvironmentConfigurationSpec extends ObjectBehavior
{
    public function let(): void
    {
        putenv('STRING=string');
        putenv('INT=5');
        putenv('BOOL_TRUE=yes');
        putenv('BOOL_FALSE=0');
        putenv('BOOL_INVALID=maybe');
        putenv('FLOAT=3.14');
    }

    public function it_returns_defaults(): void
    {
        $this->string('MISSING', 'default')->shouldEqual('default');
        $this->int('MISSING', 2)->shouldEqual(2);
        $this->boolean('MISSING', false)->shouldEqual(false);
        $this->float('MISSING', 2.71)->shouldEqual(2.71);
    }

    public function it_returns_from_env(): void
    {
        $this->string('STRING', 'default')->shouldEqual('string');
        $this->int('INT', 2)->shouldEqual(5);
        $this->boolean('BOOL_TRUE', false)->shouldEqual(true);
        $this->float('FLOAT', 2.71)->shouldEqual(3.14);
    }

    public function it_accepts_all_for_string(): void
    {
        $this->shouldNotThrow(UnexpectedType::class)->during('string', [
            'INT',
            '1',
        ]);
        $this->shouldNotThrow(UnexpectedType::class)->during('string', [
            'BOOL_TRUE',
            '2',
        ]);
        $this->shouldNotThrow(UnexpectedType::class)->during('string', [
            'FLOAT',
            '3',
        ]);
    }

    public function it_throws_on_invalid_int(): void
    {
        $this->shouldThrow(UnexpectedType::class)->during('int', ['STRING', 1]);
        $this->shouldThrow(UnexpectedType::class)->during('int', [
            'BOOL_TRUE',
            2,
        ]);
        $this->shouldThrow(UnexpectedType::class)->during('int', ['FLOAT', 3]);
    }

    public function it_throws_on_invalid_float(): void
    {
        $this->shouldThrow(UnexpectedType::class)->during('float', [
            'STRING',
            2.1,
        ]);
        $this->shouldThrow(UnexpectedType::class)->during('float', [
            'BOOL_TRUE',
            2.2,
        ]);
        $this->shouldNotThrow(UnexpectedType::class)->during('float', [
            'INT',
            2.3,
        ]);
    }

    public function it_throws_on_invalid_bool(): void
    {
        $this->shouldThrow(UnexpectedType::class)->during('boolean', [
            'STRING',
            true,
        ]);
        $this->shouldThrow(UnexpectedType::class)->during('boolean', [
            'INT',
            true,
        ]);
        $this->shouldThrow(UnexpectedType::class)->during('boolean', [
            'FLOAT',
            true,
        ]);
        $this->shouldThrow(UnexpectedType::class)->during('boolean', [
            'BOOL_INVALID',
            true,
        ]);
    }
}
