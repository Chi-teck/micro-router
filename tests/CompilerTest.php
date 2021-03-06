<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Compiler;
use MicroRouter\Exception\CompilerException;
use MicroRouter\Route;
use MicroRouter\RouteCollection;
use MicroRouter\Tests\Cache\MemoryCache;
use MicroRouter\Tests\Cache\NullCache;
use PHPUnit\Framework\TestCase;

final class CompilerTest extends TestCase
{
    private Compiler $compiler;

    public function setUp(): void
    {
        $this->compiler = new Compiler(new NullCache(), 'route_map');
    }

    public function testSimplePath(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example',
            handler: 'test',
        );

        $map = $this->compiler->compile($routes);

        $expected_map['static']['GET'] = ['/example' => 'example'];
        $expected_map['full'][] = [
            'name' => 'example',
            'prefix' => '/example',
            'pattern' => '#^/example$#',
        ];
        self::assertSame($expected_map, $map);
    }

    public function testPathWithParameters(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{id}/edit',
            handler: 'test',
        );

        $map = $this->compiler->compile($routes);

        $expected_map['static'] = [];
        $expected_map['full'][] = [
            'name' => 'example',
            'prefix' => '/example/',
            'pattern' => '#^/example/(?P<id>[^/]+)/edit$#',
        ];
        self::assertSame($expected_map, $map);
    }

    public function testPathWithRequirements(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{id}',
            handler: 'test',
            requirements: ['id' => '[a-z]{5}'],
        );

        $map = $this->compiler->compile($routes);

        $expected_map['static'] = [];
        $expected_map['full'][] = [
            'name' => 'example',
            'prefix' => '/example/',
            'pattern' => '#^/example/(?P<id>[a-z]{5})$#',
        ];
        self::assertSame($expected_map, $map);
    }

    public function testNonStringRequirement(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{id}',
            handler: 'test',
            requirements: ['id' => 123],
        );
        $expected_exception = new CompilerException('Wrong pattern for the "id" parameter of the "example" route.');
        $this->expectExceptionObject($expected_exception);
        $this->compiler->compile($routes);
    }

    public function testWrongRequirementPattern(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{id}',
            handler: 'test',
            requirements: ['id' => ')('],
        );
        $expected_exception = new CompilerException('Wrong pattern for the "id" parameter of the "example" route.');
        $this->expectExceptionObject($expected_exception);
        $this->compiler->compile($routes);
    }

    public function testOptionalParametersBeforeMandatoryParameters(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{foo}/{bar}',
            handler: 'test',
            defaults: ['foo' => 123],
        );
        $expected_exception = new CompilerException('Optional parameters must gor after mandatory parameters.');
        $this->expectExceptionObject($expected_exception);
        $this->compiler->compile($routes);
    }

    public function testWrongParameterName(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(
            methods: ['GET'],
            path: '/example/{aaa.bbb}',
            handler: 'test',
        );
        $expected_exception = new CompilerException('Could not compile path for the "example" route');
        $this->expectExceptionObject($expected_exception);
        $this->compiler->compile($routes);
    }


    public function testCaching(): void
    {
        $compiler = new Compiler(new MemoryCache(), 'route_map');

        $routes_1 = new RouteCollection();
        $routes_1['foo'] = Route::create('GET', '/foo', 'test');
        $map_1 = $compiler->compile($routes_1);

        $routes_2 = new RouteCollection();
        $routes_2['bar'] = Route::create('GET', '/bar', 'test');
        $map_2 = $compiler->compile(new RouteCollection());

        // Second collection must not be actually compiled because of cache.
        self::assertSame($map_1, $map_2);
    }

    public function testCacheKeyLengthValidation(): void
    {
        $compiler = new Compiler(new MemoryCache(), 'route_map');

        $map = $compiler->compile(new RouteCollection('default'));
        self::assertSame(['static' => [], 'full' => []], $map);

        $this->expectExceptionObject(new CompilerException('Wrong cache key'));
        $compiler->compile(new RouteCollection('wrong name'));
    }
}
