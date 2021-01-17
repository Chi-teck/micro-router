<?php
declare(strict_types=1);

namespace MicroRouter\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface MatcherInterface
{
    /**
     * Matches PSR-7 server request with a collection of routes..
     */
    public function match(ServerRequestInterface $request, RouteCollectionInterface $routes): RoutingResultInterface;
}
