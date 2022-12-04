<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Exception;
use Meteia\ValueObjects\Identity\FilesystemPath;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \Meteia\DependencyInjection\Definitions
 */
class DefinitionsSpec extends ObjectBehavior
{
    public function it_loads_valid_definitions(): void
    {
        $examples = new FilesystemPath(__DIR__, 'Stubs', 'Valid', '*', 'DependencyInjection.php');
        $this->glob($examples)->shouldHaveCount(1);
    }

    public function it_throws_on_invalid_definitions(): void
    {
        $examples = new FilesystemPath(__DIR__, 'Stubs', 'Invalid', '*', 'DependencyInjection.php');
        $this->shouldThrow(Exception::class)->during('glob', [$examples]);
    }
}
