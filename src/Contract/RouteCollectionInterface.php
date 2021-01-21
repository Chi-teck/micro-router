<?php
declare(strict_types=1);

namespace MicroRouter\Contract;

interface RouteCollectionInterface extends \IteratorAggregate, \Countable, \ArrayAccess
{
    public const UNNAMED_ROUTE_PREFIX = 'unnamed_';

    public function get(string $name): RouteInterface;

    public function has(string $name): bool;

    public function add(RouteInterface $route, ?string $name = null): void;

    public function remove(string $name): void;

    public function addCollection(self $collection): void;
}
