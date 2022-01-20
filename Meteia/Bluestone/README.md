# Meteia Bluestone

_Documentation is a work-in-progress as the overall design is still in flux._

## Requirements

* PHP ~7.0
* Dependency Injection ([ContainerInterface](https://github.com/container-interop/container-interop#projects-implementing-containerinterface))

## Definitions

### Views

Views use [Hints](#Hints) to prepare a [Context](#Context) for use by a [Template](#Template).

In Bluestone, a View is provided [Hints](#Hints) which are used by the view to fetch or build any additional information.

If you find yourself passing more than a few hints into a View you might,

* be doing too little in the View; try using the View to fetch more information.
* have unrelated elements in the [Template](#Template); try to keep [views](#View) and [templates](#Template) as focused as possible.

In only the most basic cases should your [View](#View) be directly passing the hints through. Having many "empty" View classes should be a warning that something is off.

Optimally, views should be constructed with a dependency injection framework so that it's easy for you to provide data and caching mechanisms to each view.

### Context

An associative array prepared by the [View](#View). It's expected that a Context will be significantly larger than the [Hints](#Hints) given to the [View](#View).

Most engines will expose the keys as variables directly. For example, the PHP engine will take a context of `['title' => 'Dashboard']` and make `$title` available in the [Template](#Template).

### Hints

Hints given to a [View](#View) should be the very minimum information needed for the [View](#View) to request additional information.

### Template

Templates represent the form of the final output. In most cases these are going to be HTML and use whatever the template language used by the selected [Engine](#Engine).

### Engine

The internal process that renders the [Template](#Template) with the [Context](#Context) in the expected scope. It also provides for rendering another [View](#View) from within a [Template](#Template) for nesting.


## Engines

### PHP

There should be a PHP class for every [Template](#Template).

```
<TemplateName>.php  -  Contains a single class implementing View
<TemplateName>.tpl  -  The template that will be rendered
```

For example,
```
* /                             ( project root )
    * Dashboard.php             ( PHP Class: \Dashboard )
    * Dashboard.tpl
    * QuickDisconnect           ( autoloading, via PSR-4 for example )
        * Home
            * Views
                * Home.php      ( PHP Class: \QuickDisconnect\Home\Views\Home )
                * Home.tpl

```

[PSR-4](http://www.php-fig.org/psr/psr-4/) is strongly recommended. However, if the class name and filename follow the above pattern, various class loading schemes should work.

#### Usage

```php
<?php
use Meteia\Bluestone\Contracts\Engine;
use Meteia\Bluestone\Engines\Php;

require join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions([
    Engine::class => function (\Interop\Container\ContainerInterface $container) {
        return new Php($container);
    },
]);
$container = $builder->build();

$engine = $container->get(Engine::class);
echo $engine->render(\Meteia\Examples\Homepage\Views\Index::class, ['title' => 'this is the title']);
```
