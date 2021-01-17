<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Compiler;
use MicroRouter\Contract\Exception\MatcherExceptionInterface;
use MicroRouter\Matcher;
use MicroRouter\Route;
use MicroRouter\RouteCollection;
use MicroRouter\RoutingResult;
use MicroRouter\Tests\Cache\NullCache;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * A test for a "real" application routing.
 */
final class ShopTest extends TestCase
{

    private Matcher $matcher;


    public function setUp(): void
    {
        $this->matcher = new Matcher(new Compiler(new NullCache()));
    }

    /**
     * @dataProvider routeDataProvider()
     */
    public function testRoute(ServerRequest $request, string $expectedRouteName, ?RoutingResult $expectedResult): void
    {
        try {
            $result = $this->matcher->match($request, self::getRoutes());
            if ($result->getRouteName() != $expectedRouteName) {
                $result = null;
            }
        }
        catch (MatcherExceptionInterface) {
            $result = null;
        }
        self::assertEquals($expectedResult, $result);
    }

    public function routeDataProvider(): array
    {
        $data = [];
        foreach (self::getRoutes() as $route_name => $route) {
            foreach ($route->getOptions()['requests'] as $request => $parameters) {
                [$method, $path] = \explode('->', $request);
                $data[$route_name . ':' . $request] = [
                    new ServerRequest($method, 'http://example.com' . $path),
                    $route_name,
                    \is_array($parameters) ? new RoutingResult($route_name, $route, $parameters) : null,
                ];
            }
        }
        return $data;
    }

    private static function getRoutes(): RouteCollection
    {
        $definitions = require __DIR__ . '/fixtures/shop-routes.php';
        $routes = new RouteCollection();
        foreach ($definitions as $name => $definition) {
            $routes[$name] = new Route(
                methods: $definition['methods'] ?? ['GET'],
                path: $definition['path'],
                handler: 'test',
                requirements: $definition['requirements'] ?? [],
                defaults: $definition['defaults'] ?? [],
                options: ['requests' => $definition['requests'] ?? []],
            );
        }

        return $routes;
    }
}
