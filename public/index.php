<?php

declare(strict_types=1);

use Meteia\Bootstrap\ApplicationNamespace;
use Meteia\Bootstrap\ApplicationPath;
use Meteia\Bootstrap\ApplicationPublicDir;
use Meteia\Bootstrap\MeteiaKernel;
use Meteia\Http\Middleware\ParseBody;

(function (): void {
    require implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

    $namespace = new ApplicationNamespace('ExampleApp');
    $path = new ApplicationPath(__DIR__, '..', 'ExampleApp');
    $publicDir = new ApplicationPublicDir(__DIR__, '..', 'public');

    $kernel = new MeteiaKernel($namespace, $path, $publicDir);
    $kernel->run([ParseBody::class]);
})();
