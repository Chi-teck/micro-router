<?php
declare(strict_types=1);

namespace MicroRouter\Contract;

interface RouteCollectionInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    public const UNNAMED_ROUTE_PREFIX = 'unnamed_';

    /**
     * Returns a string that uniquely identifies the collection.
     *
     * Since the name of the collection can be used as cache key it must consist
     * of the characters A-Z, a-z, 0-9, _, and . in any order in UTF-8 encoding
     * and a length from 1 to 64 characters.
     *
     * @see https://www.php-fig.org/psr/psr-6/#definitions
     */
    public function getName(): string;

    public function get(string $name): RouteInterface;

    public function has(string $name): bool;

    public function add(RouteInterface $route, ?string $name = null): void;

    public function remove(string $name): void;

    public function addCollection(self $collection): void;
}
