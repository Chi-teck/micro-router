<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Exception\RouteNotFoundException;
use MicroRouter\Route;
use MicroRouter\RouteCollection;
use PHPUnit\Framework\TestCase;

final class RouteCollectionTest extends TestCase
{
    public function testConstructor(): void
    {
        $routes = ['example' => new Route(['GET'], '/example', 'ExampleHandler')];
        $collection = new RouteCollection($routes);
        self::assertRouteCollection($routes, $collection);
    }

    public function testGetRoute(): void
    {
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection->add($route, 'example');

        self::assertSame($route, $collection->get('example'));
        self::assertSame($route, $collection['example']);

        $this->expectExceptionObject(new RouteNotFoundException('Route "not-existing-route" does not exist.'));
        $collection->get('not-existing-route');
    }

    public function testHasRoute(): void
    {
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection->add($route, 'example');

        self::assertTrue($collection->has('example'));
        self::assertFalse($collection->has('non_existing_route'));
        self::assertTrue(isset($collection['example']));
        self::assertFalse(isset($collection['non_existing_route']));
    }

    public function testAddRoute(): void
    {
        // -- Named route.
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection->add($route, 'example');

        self::assertRouteCollection(['example' => $route], $collection);

        // -- Unnamed route.
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection->add($route);

        self::assertMatchesRegularExpression('/^unnamed_.{32}$/', $collection->getIterator()->key());
        self::assertSame($route, $collection->getIterator()->current());

        // -- Named route (array access).
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection['example'] = $route;

        self::assertRouteCollection(['example' => $route], $collection);

        // -- Unnamed route (array access).
        $collection = new RouteCollection();
        $route = new Route(['GET'], '/example', 'ExampleHandler');
        $collection[] = $route;

        self::assertMatchesRegularExpression('/^unnamed_.{32}$/', $collection->getIterator()->key());
        self::assertSame($route, $collection->getIterator()->current());
    }

    public function testRemoveRoute(): void
    {
        $collection = self::createCollection();
        $collection->remove('example');
        self::assertRouteCollection([], $collection);

        $collection = self::createCollection();
        unset($collection['example']);
        self::assertRouteCollection([], $collection);
    }

    public function testCountable(): void
    {
        $collection = self::createCollection();
        self::assertCount(1, $collection);

        $collection->remove('example');
        self::assertCount(0, $collection);
    }

    public function testIterable(): void
    {
        $routes = [
            'article.view' => new Route(['GET'], '/article/{id}', 'ArticleViewController'),
            'article.edit' => new Route(['PUT'], '/article/{id}/edit', 'ArticleEditController'),
            'article.delete' => new Route(['DELETE'], '/article/{id}/delete', 'ArticleDeleteController'),
        ];
        $collection = new RouteCollection($routes);

        foreach ($collection as $name => $route) {
            self::assertSame($routes[$name], $route);
        }
    }

    public function testStringable(): void
    {
        $expected_output = <<< 'TXT'
            example
            ━━━━━━━
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
        self::assertSame($expected_output, (string) self::createCollection());
    }

    public function testAddCollection(): void
    {
        $collection_1 = new RouteCollection();

        $route_1 = new Route(['GET'], 'example/1', 'Example');
        $collection_1->add($route_1, 'route_1', 10);

        $route_2 = new Route(['GET'], 'example/2', 'Example');
        $collection_1->add($route_2, 'route_2', 20);

        $route_3 = new Route(['GET'], 'example/3', 'Example');
        $collection_1->add($route_3, 'route_3', 30);

        $collection_2 = new RouteCollection();

        // This one should replace $route_3 in $collection_1.
        $route_3_new = new Route(['GET'], 'example/333', 'Example');
        $collection_2->add($route_3_new, 'route_3', 100);

        // This one should be appended to the $collection_1.
        $route_4 = new Route(['GET'], 'example/444', 'Example');

        $collection_1->add($route_4, 'route_4', 150);

        $collection_1->addCollection($collection_2);

        $expected_routes = [
            'route_3' => $route_3_new,
            'route_1' => $route_1,
            'route_2' => $route_2,
            'route_4' => $route_4,
        ];
        self::assertRouteCollection($expected_routes, $collection_1);
    }

    private static function createCollection(): RouteCollection
    {
        return new RouteCollection(
            ['example' => new Route(['GET'], '/example', 'ExampleHandler')],
        );
    }

    private static function assertRouteCollection(array $expectedRoutes, RouteCollection $collection): void
    {
        self::assertSame($expectedRoutes, \iterator_to_array($collection));
    }
}
