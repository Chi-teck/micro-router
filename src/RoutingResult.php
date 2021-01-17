<?php
declare(strict_types=1);

namespace MicroRouter;

use MicroRouter\Contract\RouteInterface;
use MicroRouter\Contract\RoutingResultInterface;

final class RoutingResult implements RoutingResultInterface
{
    public function __construct(
        private ?string $routeName,
        private RouteInterface $route,
        private array $parameters,
    ) {}

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
