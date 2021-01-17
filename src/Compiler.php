<?php
declare(strict_types=1);

namespace MicroRouter;

use MicroRouter\Contract\RouteCollectionInterface;
use MicroRouter\Contract\RouteInterface;
use MicroRouter\Exception\CompilerException;
use Psr\SimpleCache\CacheInterface;

final class Compiler
{
    public function __construct(
        private CacheInterface $cache,
        private string $cacheKey = 'route_map',
    ) {}

    /**
     * Returns compiled route map as array. This map is meant only for use in
     * MicroRouter\Matcher. The structure of the map is not part of public API
     * as it may change overtime. If you need to create a custom matcher, create
     * own compiler for it.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function compile(RouteCollectionInterface $routes): array
    {
        $map = $this->cache->get($this->cacheKey);
        if ($map === null) {
            $map = $this->doCompile($routes);
            $this->cache->set($this->cacheKey, $map);
        }
        return $map;
    }

    /**
     * @throws \MicroRouter\Exception\CompilerException
     */
    private function doCompile(RouteCollectionInterface $routes): array
    {
        $static_map = [];
        $full_map = [];
        // A pattern for ending optional argument.
        $pattern = '#/\{([^{]+)\}$#';
        foreach ($routes as $route_name => $route) {
            $path = $route->getPath();

            if (!\str_contains($path, '{')) {
                foreach ($route->getMethods() as $method) {
                    $static_map[$method][$path] = $route_name;
                }
                // Do not stop here as the route still needs to be added to the
                // dynamic map to collect allowed methods when 405 error
                // happens.
            }

            self::validateRequirements($route_name, $route);

            $full_map[] = self::compileRoute($route_name, $route, $path);

            $defaults = $route->getDefaults();

            $derived_path = $path;
            $is_mandatory = false;
            while (\preg_match($pattern, $derived_path, $matches)) {
                $derived_path = \preg_replace($pattern, '', $derived_path);
                if (\array_key_exists($matches[1], $defaults)) {
                    if ($is_mandatory) {
                        throw new CompilerException('Optional parameters must gor after mandatory parameters.');
                    }
                    $full_map[] = self::compileRoute($route_name, $route, $derived_path);
                }
                else {
                    $is_mandatory = true;
                }
            }
        }

        // Typically most visited site URLs have shorter path. So let's put
        // them to the top of the map to speed up matching.
        \usort(
            $full_map,
            static function (array $record_a, array $record_b): int {
                $path_a = \preg_replace('#\(.+\)#', '--', $record_a['pattern']);
                $path_b = \preg_replace('#\(.+\)#', '--', $record_b['pattern']);
                return \strlen($path_a) <=> \strlen($path_b);
            },
        );

        return ['static' => $static_map, 'full' => $full_map];
    }

    /**
     * @throws \MicroRouter\Exception\CompilerException
     */
    private static function compileRoute(string $routeName, RouteInterface $route, string $path): array
    {
        $record = ['name' => $routeName];

        [$record['prefix']] = \explode('{', $path);
        $pattern = \preg_replace_callback(
            '#{(.+?)}#',
            static function (array $matches) use ($routeName, $route): string {
                $name = $matches[1];
                if (!\preg_match('#^[a-z0-9_-]+$#i', $name)) {
                    throw new CompilerException(\sprintf('Could not compile path for the "%s" route', $routeName));
                }
                $parameter_pattern = $route->getRequirements()[$name] ?? '[^/]+';
                return \sprintf('(?P<%s>%s)', $name, $parameter_pattern);
            },
            $path,
        );
        $record['pattern'] = '#^' . $pattern . '$#';

        return $record;
    }

    /**
     * @throws \MicroRouter\Exception\CompilerException
     */
    private static function validateRequirements(string $routeName, RouteInterface $route): void {
        $requirements = $route->getRequirements();
        foreach ($requirements as $name => $requirement) {
            if (!\is_string($requirement) || @\preg_match("#^$requirement$#", '') === false) {
                $message = \sprintf('Wrong pattern for the "%s" parameter of the "%s" route.', $name, $routeName);
                throw new CompilerException($message);
            }
        }
    }
}
