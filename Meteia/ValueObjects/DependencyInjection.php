<?php

declare(strict_types=1);

use Hidehalo\Nanoid\Client;
use Meteia\ValueObjects\Identity\CorrelationId;

return [
    CorrelationId::class => function () {
        $client = new Client();
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        return new CorrelationId(implode('-', str_split($client->formatedId($alphabet, 16), 4)));
    },
];
