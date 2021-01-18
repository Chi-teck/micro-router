<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Route;
use MicroRouter\RoutingResult;
use PHPUnit\Framework\TestCase;

final class RoutingResultTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $result = new RoutingResult(
            routeName: $route_name = 'example',
            route: $route = new Route(['GET'], '/example/{id}', 'ExampleController'),
            parameters: $parameters = ['id' => 123],
        );

        self::assertSame($route_name, $result->getRouteName());
        self::assertSame($route, $result->getRoute());
        self::assertSame($parameters, $result->getParameters());
    }

    public function testWithParameters(): void
    {
        $result = new RoutingResult(
            routeName: $route_name = 'example',
            route: $route = new Route(['GET'], '/example/{id}', 'ExampleController'),
            parameters: $parameters = ['id' => 123],
        );

        $cloned_result = $result->withParameters(['id' => 456, 'foo' => 'bar']);

        self::assertSame($result->getRouteName(), $cloned_result->getRouteName());
        self::assertSame($result->getRoute(), $cloned_result->getRoute());
        self::assertSame($result->getParameters(), ['id' => 123]);
        self::assertSame($cloned_result->getParameters(), ['id' => 456, 'foo' => 'bar']);
    }
}
