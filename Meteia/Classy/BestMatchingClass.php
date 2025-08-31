<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Meteia\Classy\Errors\MatchingClassNotFound;

class BestMatchingClass
{
    public function in(string $name, string $implementing, array $postFixes = []): string
    {
        $pathParts = explode('\\', trim($name, '\\'));
        $contentAndPathNames = array_map(static fn($part) => '\\' . $part, [
            $pathParts[1],
            ...\array_slice($pathParts, 3),
        ]);
        $postFixes = array_merge($postFixes, [''], $contentAndPathNames);

        // FIXME: Might be better to search shorter to longest? Bisect?
        for ($i = \count($pathParts); $i > 1; --$i) {
            $possibleClassName = implode('\\', \array_slice($pathParts, 0, $i));

            $possibleClassNames = array_map(static fn($postfix) => $possibleClassName . $postfix, $postFixes);

            foreach ($possibleClassNames as $possibleClassName) {
                if (is_subclass_of($possibleClassName, $implementing)) {
                    return $possibleClassName;
                }
            }
        }

        throw new MatchingClassNotFound('No Matching Class Found');
    }
}
