<?php

declare(strict_types=1);

namespace Meteia\Classy\Matched;

use Meteia\Classy\Errors\MatchingClassNotFound;
use Meteia\Classy\MatchedClass;
use Meteia\DependencyInjection\Container;

final readonly class MissingClass implements MatchedClass
{
    #[\Override]
    public function resolveIn(Container $container): object
    {
        throw new MatchingClassNotFound('No Matching Class Found');
    }
}
