<?php

declare(strict_types=1);

namespace Meteia\DependencyInjection;

use Meteia\DependencyInjection\Fixtures\ClassWithoutConstructor;
use Meteia\DependencyInjection\Fixtures\Definitions;
use Meteia\DependencyInjection\Fixtures\FactoryOutput;
use Meteia\DependencyInjection\Fixtures\FactoryProduct;
use Meteia\DependencyInjection\Fixtures\InnerDependency;
use PHPUnit\Framework\TestCase;

class ReflectionContainerTest extends TestCase
{
    public function testCall()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertEquals($container->call(fn () => 1), 1);
    }

    public function testSet()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertEquals('', $container->get(InnerDependency::class)->option);

        $input = new InnerDependency(new \DateTime(), 'testing');
        $container->set(InnerDependency::class, $input);
        $this->assertEquals('testing', $container->get(InnerDependency::class)->option);
    }

    public function testSetCallable()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertEquals('', $container->get(InnerDependency::class)->option);

        $container->set(InnerDependency::class, fn () => new InnerDependency(new \DateTime(), 'testing'));
        $this->assertEquals('testing', $container->get(InnerDependency::class)->option);
    }

    public function testClassWithoutConstructor()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertInstanceOf(ClassWithoutConstructor::class, $container->get(ClassWithoutConstructor::class));
    }

    public function testFactoryOutput()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertInstanceOf(FactoryProduct::class, $container->get(FactoryOutput::class));
    }

    public function testSyncDeps()
    {
        $container = new ReflectionContainer(Definitions::get());
        $this->assertInstanceOf(InnerDependency::class, $container->get(InnerDependency::class));
    }

    public function testCallable()
    {
        $container = new ReflectionContainer(Definitions::get());

        $callable = fn (InnerDependency $id, string $suffix) => $id->option . $suffix;

        $this->assertEquals('151515', $container->call($callable, ['suffix' => '151515']));
    }

    public function testOldCallable()
    {
        $container = new ReflectionContainer(Definitions::get());

        $id = $container->get(InnerDependency::class);

        $this->assertEquals('4321', $container->call([$id, 'reverse'], ['text' => '1234']));
    }
}
