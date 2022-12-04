<?php

declare(strict_types=1);

$test = [
    \Exception::class => fn (): Exception => new \Exception('Testing'),
];
