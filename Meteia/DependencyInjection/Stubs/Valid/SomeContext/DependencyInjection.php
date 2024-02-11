<?php

declare(strict_types=1);

return [
    Exception::class => static fn (): Exception => new Exception('Testing'),
];
