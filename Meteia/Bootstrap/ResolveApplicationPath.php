<?php

declare(strict_types=1);

namespace Meteia\Bootstrap;

final readonly class ResolveApplicationPath
{
    public function from(string ...$paths): ApplicationPath
    {
        $joined = implode(\DIRECTORY_SEPARATOR, array_map(
            static fn(string $p): string => rtrim($p, \DIRECTORY_SEPARATOR),
            $paths,
        ));
        $real = realpath($joined);
        if ($real === false) {
            throw new InvalidApplicationPath($joined);
        }

        return new ApplicationPath($real);
    }
}
