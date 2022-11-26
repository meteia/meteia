<?php

declare(strict_types=1);

namespace Meteia\Http;

trait ClassBasedName
{
    private function classBasedName($class = null): string
    {
        $names = explode('\\', $class ?? get_called_class());

        // Always remove the first namespace
        array_shift($names);

        $names = array_diff($names, ['Http', 'Endpoints']);

        return implode('_', $names);
    }
}
