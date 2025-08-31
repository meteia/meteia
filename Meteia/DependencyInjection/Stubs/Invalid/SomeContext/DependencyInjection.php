<?php

declare(strict_types=1);

$test = [
    Exception::class => static fn(): Exception => new Exception('Testing'),
];
