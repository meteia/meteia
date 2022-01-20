<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

class Bucket
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
