<?php
declare(strict_types=1);

namespace MicroRouter\Tests;

use MicroRouter\Contract\Exception\MatcherExceptionInterface;
use MicroRouter\Contract\Exception\MethodNotAllowedInterface;
use MicroRouter\Contract\Exception\ResourceNotFoundInterface;
use MicroRouter\Contract\RoutingResultInterface;
use MicroRouter\Exception\MethodNotAllowedException;
use MicroRouter\Exception\ResourceNotFoundException;
use MicroRouter\Matcher;
use MicroRouter\Route;
use MicroRouter\RouteCollection;
use MicroRouter\RoutingResult;
use MicroRouter\Tests\Cache\MemoryCache;
use MicroRouter\Tests\Cache\NullCache;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

final class MatcherTest extends TestCase
{
    private Matcher $matcher;

    public function setUp(): void
    {
        $this->matcher = Matcher::create(new NullCache());
    }

    public function testRootPath(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/', 'test');

        $expected_result = new RoutingResult('example', $routes['example'], []);
        $this->assertMatchResult($expected_result, '/', $routes);
        // @todo test root request without slash.
    }

    public function testSimplePath(): void
    {
        $routes = new RouteCollection();
        $routes['route_1'] = new Route(['GET'], '/example', 'test');
        $routes['route_2'] = new Route(['GET'], '/example/', 'test');
        $routes['route_3'] = new Route(['GET'], '/example/123', 'test');

        $expected_result = new RoutingResult('route_1', $routes['route_1'], []);
        $this->assertMatchResult($expected_result, '/example', $routes);

        $expected_result = new RoutingResult('route_2', $routes['route_2'], []);
        $this->assertMatchResult($expected_result, '/example/', $routes);

        $expected_result = new RoutingResult('route_3', $routes['route_3'], []);
        $this->assertMatchResult($expected_result, '/example/123', $routes);

        $this->assertMatchResult(null, '/ example', $routes);
        $this->assertMatchResult(null, '/example/1', $routes);
        $this->assertMatchResult(null, '/example1', $routes);
        $this->assertMatchResult(null, '/eXample', $routes);
    }

    public function testMandatoryParameters(): void
    {
        $routes = new RouteCollection();
        $routes['route_1'] = new Route(['GET'], '/article/{id}', 'test');
        $routes['route_2'] = new Route(['GET'], '/article/{k1}_{k2}/example-{k3}', 'test');
        $routes['route_3'] = new Route(['GET'], '/{id}/article', 'test');

        $expected_result = new RoutingResult('route_1', $routes['route_1'], ['id' => 1]);
        $this->assertMatchResult($expected_result, '/article/1', $routes);

        $expected_result = new RoutingResult('route_2', $routes['route_2'], ['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3']);
        $this->assertMatchResult($expected_result, '/article/v1_v2/example-v3', $routes);

        $expected_result = new RoutingResult('route_3', $routes['route_3'], ['id' => '456']);
        $this->assertMatchResult($expected_result, '/456/article', $routes);

        $this->assertMatchResult(null, '/article', $routes);
        $this->assertMatchResult(null, '/article/123/', $routes);
        $this->assertMatchResult(null, '/article/v1-v2/example-z3', $routes);
    }

    public function testOptionalParameters(): void
    {
        $routes = new RouteCollection();
        // @todo convert optional parameters to string?
        $routes['route_1'] = new Route(['GET'], '/article/{id}', 'test', [], ['id' => '555']);
        $routes['route_2'] = new Route(['GET'], '/products/search/{feature}/{value}', 'test', [], ['feature' => 'color', 'value' => 'red']);

        $expected_result = new RoutingResult('route_1', $routes['route_1'], ['id' => '10']);
        $this->assertMatchResult($expected_result, '/article/10', $routes);

        $expected_result = new RoutingResult('route_1', $routes['route_1'], ['id' => '555']);
        $this->assertMatchResult($expected_result, '/article', $routes);

        $expected_result = new RoutingResult('route_2', $routes['route_2'], ['feature' => 'color', 'value' => 'red']);
        $this->assertMatchResult($expected_result, '/products/search/color', $routes);

        $expected_result = new RoutingResult('route_2', $routes['route_2'], ['feature' => 'size', 'value' => 'big']);
        $this->assertMatchResult($expected_result, '/products/search/size/big', $routes);
    }

    public function testOptionalParameterWithNullAsDefaultValue(): void {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', [], ['id' => null]);

        $expected_result = new RoutingResult('example', $routes['example'], ['id' => null]);
        $this->assertMatchResult($expected_result, '/example', $routes);
    }

    public function testOptionalParameterWithIntegerAsDefaultValue(): void {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', [], ['id' => 123]);

        $result = $this->matchPath('/example', $routes);
        self::assertSame(123, $result->getParameters()['id']);
    }

    public function testOptionalParameterWithArrayAsDefaultValue(): void {
        $routes = new RouteCollection();
        $data = ['id' => 123];
        $routes['example'] = new Route(['GET'], '/example/{data}', 'test', [], ['data' => $data]);

        $result = $this->matchPath('/example', $routes);
        self::assertSame($data, $result->getParameters()['data']);
    }

    public function testDefaultParameterThatIsNotInPath(): void {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example', 'test', [], ['id' => 123]);

        $result = $this->matchPath('/example', $routes);
        self::assertSame(['id' => 123], $result->getParameters());
    }

    public function testDefaultParameterWithRequirements(): void {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', ['id' => '\d+'], ['id' => 123]);

        $result = $this->matchPath('/example', $routes);
        self::assertSame(['id' => 123], $result->getParameters());
    }

    public function testOptionalParameterWithObjectAsDefaultValue(): void {
        $routes = new RouteCollection();
        $data = (object) ['id' => 123];
        $routes['example'] = new Route(['GET'], '/example/{data}', 'test', [], ['data' => $data]);

        $result = $this->matchPath('/example', $routes);
        self::assertSame($data, $result->getParameters()['data']);
    }

    public function testSimpleRequirements(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', ['id' => '.\d+']);

        $expected_result = new RoutingResult('example', $routes['example'], ['id' => '123']);
        $this->assertMatchResult($expected_result, '/example/123', $routes);

        $this->assertMatchResult(null, '/example/abc', $routes);
    }

    public function testRequirementsWithCurlyBraces(): void
    {
        $routes = new RouteCollection();
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', ['id' => '5{3}']);

        $expected_result = new RoutingResult('example', $routes['example'], ['id' => '555']);
        $this->assertMatchResult($expected_result, '/example/555', $routes);

        $this->assertMatchResult(null, '/example/abc', $routes);
        $this->assertMatchResult(null, '/example/55', $routes);
        $this->assertMatchResult(null, '/example/5555', $routes);
    }

    public function testDefaults(): void
    {
        $routes = new RouteCollection();
        $defaults = [
            'id' => 123,
            'extra_data' => ['foo' => 'bar'],
        ];
        $routes['example'] = new Route(['GET'], '/example/{id}', 'test', [], $defaults);

        $expected_result = new RoutingResult('example', $routes['example'], $defaults);
        $result = $this->matchPath('/example', $routes);
        self::assertEquals($expected_result, $result);
    }

    /**
     * @dataProvider policyDataProvider().
     */
    public function testMethodNotAllowed(array $routeMethods, string $requestMethod, bool $expectedResult): void
    {
        $routes = new RouteCollection();
        $routes[] = new Route($routeMethods, '/example', null);
        $request = new ServerRequest($requestMethod, 'https://example.com/example');

        try {
            $this->matcher->match($request, $routes);
            $result = true;
        }
        catch (MethodNotAllowedInterface) {
            $result = false;
        }
        catch (ResourceNotFoundInterface) {
            $result = true;
        }

        self::assertSame($expectedResult, $result);
    }

    public function policyDataProvider(): array
    {
        $data['Simple match'] = [['GET'], 'GET', true];
        $data['Mismatched case #1'] = [['GET'], 'get_', false];
        $data['Mismatched case #2'] = [['get'], 'GET', false];
        $data['Low case'] = [['get'], 'get', true];
        $data['Multiple allowed methods'] = [['GET', 'POST'], 'POST', true];
        $data['Any method allowed'] = [[], 'POST', true];
        $data['Simple mismatch'] = [['DELETE'], 'POST', false];
        $data['HEAD as GET'] = [['GET'], 'HEAD', true];
        return $data;
    }

    public function testMethodAllowedWhenNoMethodsRequired(): void
    {
        $routes = new RouteCollection();
        $routes[] = new Route([], '/test', 'test');
        $request = new ServerRequest('POST', 'https://example.com/test');

        $this->assertException(null, $request, $routes);
    }

    public function testHeadAllowedWhenGetRequired(): void
    {
        $routes = new RouteCollection();
        $routes[] = new Route(['GET', 'POST'], '/test', 'test');
        $request = new ServerRequest('HEAD', 'https://example.com/test');

        $this->assertException(null, $request, $routes);
    }

    public function testAggregationOfAllowedMethods(): void
    {
        $routes = new RouteCollection();
        $routes[] = new Route(['GET', 'POST'], '/test', 'test');
        $routes[] = new Route(['PUT', 'DELETE'], '/test', 'test');
        $routes[] = new Route(['GET', 'DELETE'], '/test', 'test');
        $request = new ServerRequest('PATCH', 'https://example.com/test');

        $expected_methods = ['DELETE', 'GET', 'POST', 'PUT'];
        self::assertException(new MethodNotAllowedException($expected_methods), $request, $routes);
    }

    public function testCachingUnnamedRoutes(): void
    {
        $matcher = Matcher::create(new MemoryCache());
        $request = new ServerRequest('GET', 'https://example.com/test');
        $route = new Route(['GET'], '/test', 'test');

        $routes_1 = new RouteCollection();
        $routes_1->add($route);
        $result_1 = $matcher->match($request, $routes_1);

        $routes_2 = new RouteCollection();
        $routes_2->add($route);
        $result_2 = $matcher->match($request, $routes_2);

        self::assertTrue($routes_2->has($result_1->getRouteName()));
        self::assertTrue($routes_1->has($result_2->getRouteName()));
    }

    private function assertMatchResult(?RoutingResult $expectedResult, string $path, RouteCollection $routes): void
    {
        self::assertEquals($expectedResult, $this->matchPath($path, $routes));
    }

    private function assertException(?MatcherExceptionInterface $expectedException, ServerRequest $request, RouteCollection $routes): void
    {
        $exception = null;
        try {
            $this->matcher->match($request, $routes);
        }
        catch (ResourceNotFoundInterface | MethodNotAllowedInterface $exception) {
        }
        self::assertEquals($expectedException, $exception);
    }

    private function matchPath(string $path, RouteCollection $routes): ?RoutingResultInterface
    {
        $request = new ServerRequest('GET', 'https://example.com' . $path);
        try {
            return $this->matcher->match($request, $routes);
        }
        catch (ResourceNotFoundException) {
            return null;
        }
    }
}
