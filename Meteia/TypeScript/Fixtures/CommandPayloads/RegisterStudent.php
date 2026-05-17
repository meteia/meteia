<?php

declare(strict_types=1);

namespace Meteia\TypeScript\Fixtures\CommandPayloads;

use Meteia\Commands\Command;

final readonly class RegisterStudent implements Command
{
    public function __construct(
        public string $studentName,
        public int $age,
        public float $score,
        public bool $active,
        public ?string $nickname,
    ) {}
}
