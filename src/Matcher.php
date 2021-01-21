<?php
declare(strict_types=1);

namespace MicroRouter;

use MicroRouter\Contract\MatcherInterface;
use MicroRouter\Contract\RouteCollectionInterface;
use MicroRouter\Contract\RoutingResultInterface;
use MicroRouter\Exception\MethodNotAllowedException;
use MicroRouter\Exception\ResourceNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

final class Matcher implements MatcherInterface
{
    public function __construct(private Compiler $compiler) {}

    public static function create(CacheInterface $cache, string $cacheKeyPrefix = 'route_map'): self {
        return new self(new Compiler($cache, $cacheKeyPrefix));
    }

    /**
     * @throws \MicroRouter\Contract\Exception\ResourceNotFoundInterface
     * @throws \MicroRouter\Contract\Exception\MethodNotAllowedInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function match(ServerRequestInterface $request, RouteCollectionInterface $routes): RoutingResultInterface
    {
        $map = $this->compiler->compile($routes);

        $path = \rawurldecode($request->getUri()->getPath());
        $method = $request->getMethod();

        $route_name = $map['static'][$method][$path] ?? null;
        if ($route_name) {
            $route = $routes->get($route_name);
            if (self::isMethodAllowed($method, $route->getMethods())) {
                return new RoutingResult($route_name, $route, $route->getDefaults());
            }
        }

        $allowed_methods = [];
        foreach ($map['full'] as $record) {
            if (!\str_starts_with($path, $record['prefix'])) {
                continue;
            }
            if (!\preg_match($record['pattern'], $path, $matches)) {
                continue;
            }
            $route = $routes->get($record['name']);

            // Remove numeric matches.
            $parameters = \array_filter($matches, 'is_string', \ARRAY_FILTER_USE_KEY);
            $parameters += $route->getDefaults();

            if (!self::isMethodAllowed($method, $route->getMethods())) {
                // The 405 response must provide a complete list of allowed
                // methods collected from all matched routes.
                $allowed_methods = self::aggregateMethods($allowed_methods, $route->getMethods());
                $exception = new MethodNotAllowedException($allowed_methods);
                continue;
            }

            return new RoutingResult(
                // The name of unnamed route should never bubble up.
                \str_starts_with($record['name'], RouteCollection::UNNAMED_ROUTE_PREFIX)
                    ? null : $record['name'],
                $route,
                $parameters,
            );
        }

        throw $exception ?? new ResourceNotFoundException();
    }

    private static function isMethodAllowed(string $method, array $allowedMethods): bool
    {
        return
            // If route does not define methods any request method is acceptable.
            (\count($allowedMethods) == 0 || \in_array($method, $allowedMethods, true)) ||
            // The HEAD method is identical to GET except that the server MUST NOT
            // return a message body in the response.
            // @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
            ($method === 'HEAD' && \in_array('GET', $allowedMethods));
    }

    private static function aggregateMethods(array $processedMethods, array $routeMethods): array
    {
        $processedMethods = \array_merge($routeMethods, $processedMethods);
        $processedMethods = \array_unique($processedMethods);
        $processedMethods = \array_values($processedMethods);
        \sort($processedMethods);
        return $processedMethods;
    }
}
