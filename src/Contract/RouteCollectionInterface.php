<?php
declare(strict_types=1);

namespace MicroRouter\Contract;

interface RouteCollectionInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * Returns a string that uniquely identifies the collection.
     */
    public function getName(): string;

    public function get(string $name): RouteInterface;

    public function has(string $name): bool;

    public function add(RouteInterface $route, ?string $name = null): void;

    public function remove(string $name): void;

    public function addCollection(self $collection): void;
}
