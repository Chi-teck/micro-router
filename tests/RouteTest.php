<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Route;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $route = new Route(
            $methods = ['post', 'GET', 'Delete'],
            $path = '/example/id',
            $handler = 'ExampleHandler',
            $requirements = ['id' => '\d+'],
            $defaults = ['id' => null],
            $options = ['foo' => 'bar'],
        );

        self::assertSame(['Delete', 'GET', 'post'], $route->getMethods());
        self::assertSame($path, $route->getPath());
        self::assertSame($handler, $route->getHandler());
        self::assertSame($requirements, $route->getRequirements());
        self::assertSame($defaults, $route->getDefaults());
        self::assertSame($options, $route->getOptions());
    }

    public function testFactory(): void
    {
        $route = Route::create('GET', '/example', 'ExampleHandler', [], ['foo' => 'bar'], ['_host' => 'localhost']);
        self::assertSame(['GET'], $route->getMethods());
        self::assertSame('/example', $route->getPath());
        self::assertSame('ExampleHandler', $route->getHandler());
        self::assertSame([], $route->getRequirements());
        self::assertSame(['foo' => 'bar'], $route->getDefaults());
        self::assertSame(['_host' => 'localhost'], $route->getOptions());

        $route = Route::create('GET', '/example/{name:[a-z]+}/{page}', 'ExampleHandler', ['page' => '\d+']);
        self::assertSame(['GET'], $route->getMethods());
        self::assertSame('/example/{name}/{page}', $route->getPath());
        self::assertSame('ExampleHandler', $route->getHandler());
        self::assertSame(['page' => '\d+','name' => '[a-z]+'], $route->getRequirements());
        self::assertSame([], $route->getDefaults());
        self::assertSame([], $route->getOptions());

        $route = Route::create('GET', '/example/{name=foo:[a-z]+}/{page=1}', 'ExampleHandler');
        self::assertSame(['GET'], $route->getMethods());
        self::assertSame('/example/{name}/{page}', $route->getPath());
        self::assertSame('ExampleHandler', $route->getHandler());
        self::assertSame(['name' => '[a-z]+'], $route->getRequirements());
        self::assertSame(['name' => 'foo', 'page' => '1'], $route->getDefaults());
        self::assertSame([], $route->getOptions());
    }

    public function testWithPath(): void
    {
        $route = new Route(['GET'], '/foo', 'FooHandler');
        $new_route = $route->withPath('/bar');
        self::assertSame('/foo', $route->getPath());
        self::assertSame(['GET'], $new_route->getMethods());
        self::assertSame('/bar', $new_route->getPath());
        self::assertSame('FooHandler', $new_route->getHandler());
    }

    public function testStringable(): void
    {
        $expected_output = <<< 'TXT'
            MicroRouter\Route Object
            (
                [methods] => Array
                    (
                        [0] => GET
                    )
            
                [path] => /example
                [handler] => ExampleHandler
                [requirements] => Array
                    (
                    )
            
                [defaults] => Array
                    (
                    )
            
                [options] => Array
                    (
                    )
            
            )
            
            TXT;
        self::assertSame($expected_output, (string) new Route(['GET'], '/example', 'ExampleHandler'));
    }
}
