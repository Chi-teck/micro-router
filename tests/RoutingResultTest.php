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
}
