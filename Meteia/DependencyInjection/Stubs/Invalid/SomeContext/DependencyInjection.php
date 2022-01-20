<?php

declare(strict_types=1);

$test = [
    \Exception::class => function (): Exception {
        return new \Exception('Testing');
    },
];
