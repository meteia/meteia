<?php

declare(strict_types=1);

namespace Meteia\ValueObjects\Contracts;

interface Identifier extends \Stringable, \JsonSerializable, HasPrefix
{
    public function hex(): string;

    public function binaryHash(): string;

    public function hash(): string;
}
