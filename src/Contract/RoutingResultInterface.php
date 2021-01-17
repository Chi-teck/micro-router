<?php
declare(strict_types=1);

namespace MicroRouter\Contract;

interface RoutingResultInterface
{
    /**
     * Returns route name or null if matched route is unnamed.
     */
    public function getRouteName(): ?string;

    /**
     * Returns matched route.
     */
    public function getRoute(): RouteInterface;

    /**
     * Route parameters with values from the matched request.
     */
    public function getParameters(): array;
}
