<?php

declare(strict_types=1);

return [
    \Exception::class => fn (): Exception => new \Exception('Testing'),
];
