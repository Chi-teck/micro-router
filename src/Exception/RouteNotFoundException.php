<?php
declare(strict_types=1);

namespace MicroRouter\Exception;

use MicroRouter\Contract\Exception\RouteNotFoundInterface;

final class RouteNotFoundException extends \LogicException implements RouteNotFoundInterface
{

}
