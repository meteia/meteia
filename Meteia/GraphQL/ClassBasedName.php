<?php

declare(strict_types=1);

namespace Meteia\GraphQL;

trait ClassBasedName
{
    private function classBasedName($class = null)
    {
        $names = explode('\\', $class ?? get_called_class());

        // Always remove the first namespace
        array_shift($names);

        $names = array_diff($names, ['ApiServer', 'GraphQL', 'Types', 'Queries', 'Mutations']);

        return implode('_', $names);
    }
}
