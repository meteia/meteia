<?php

declare(strict_types=1);

namespace Meteia\Queries;

/**
 * Where a query arrives to be answered. One implementation per query type.
 *
 * @template TQuery of Query
 * @template TResult
 */
interface QueryEndpoint
{
    /**
     * @param TQuery $query
     *
     * @return TResult
     */
    public function answer(Query $query): mixed;
}
