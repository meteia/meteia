<?php

declare(strict_types=1);

namespace Meteia\Classy;

use Meteia\Classy\Errors\MatchingClassNotFound;

class BestMatchingClass
{
    public function in(string $name, string $implementing, array $postFixes = []): string
    {
        $postFixes = array_merge($postFixes, ['']);
        $pathParts = explode('\\', trim($name, '\\'));

        // FIXME: Might be better to search shorter to longest? Bisect?
        for ($i = \count($pathParts); $i > 1; --$i) {
            $possibleClassName = implode('\\', \array_slice($pathParts, 0, $i));

            $possibleClassNames = array_map(fn ($postfix) => $possibleClassName . $postfix, $postFixes);

            foreach ($possibleClassNames as $possibleClassName) {
                if (is_subclass_of($possibleClassName, $implementing)) {
                    return $possibleClassName;
                }
            }
        }

        throw new MatchingClassNotFound('No Matching Class Found');
    }
}
