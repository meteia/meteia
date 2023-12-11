<?php

declare(strict_types=1);

use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\Application\ApplicationPublicDir;
use Meteia\Application\Instance;
use Meteia\Http\Middleware\ParseBody;

(function (): void {
    require implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

    $namespace = new ApplicationNamespace('ExampleApp');
    $path = new ApplicationPath(__DIR__, '..', 'ExampleApp');
    $publicDir = new ApplicationPublicDir(__DIR__, '..', 'public');

    $application = new Instance($namespace, $path, $publicDir);
    $application->run([ParseBody::class]);
})();
