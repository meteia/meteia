<?php

declare(strict_types=1);

namespace Meteia\GraphQL\Contracts;

interface MutationField extends Field
{
    public function args(): array;
}
