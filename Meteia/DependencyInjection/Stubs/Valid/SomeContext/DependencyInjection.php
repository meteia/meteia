<?php

declare(strict_types=1);

return [
    \Exception::class => function (): Exception {
        return new \Exception('Testing');
    },
];
