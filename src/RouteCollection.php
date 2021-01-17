<?php
declare(strict_types=1);

namespace MicroRouter;

use MicroRouter\Contract\RouteCollectionInterface;
use MicroRouter\Contract\RouteInterface;
use MicroRouter\Exception\RouteNotFoundException;

final class RouteCollection implements RouteCollectionInterface
{
    public function __construct(private array $routes = []) {
        foreach ($this->routes as $name => $route) {
            // @todo test this.
            $this->add($route, \is_int($name) ? null : $name);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \IteratorAggregate|\MicroRouter\Contract\RouteInterface[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->routes);
    }


    public function get(string $name): RouteInterface
    {
        if (!$this->has($name)) {
            throw new RouteNotFoundException(\sprintf('Route "%s" does not exist.', $name));
        }
        return $this->routes[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    public function add(RouteInterface $route, ?string $name = null, int $priority = 0): void
    {
        $name ??= self::UNNAMED_ROUTE_PREFIX . \mt_rand();
        $this->routes[$name] = $route;
    }

    public function remove(string $name): void
    {
        unset($this->routes[$name]);
    }

    public function addCollection(RouteCollectionInterface $collection): void
    {
        $this->routes = $collection->routes + $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ?RouteInterface
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->add($value, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    public function __toString(): string
    {
        $output = [];
        foreach ($this->routes as $name => $route) {
            $name = \str_starts_with($name, self::UNNAMED_ROUTE_PREFIX) ? 'unnamed' : $name;
            $output[] = $name;
            $output[] = \str_repeat('━', \strlen($name));
            $output[] = $route;
        }
        return \implode(\PHP_EOL, $output);
    }
}
