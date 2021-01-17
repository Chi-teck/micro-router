<?php
declare(strict_types=1);

namespace MicroRouter\Exception;

use MicroRouter\Contract\Exception\ResourceNotFoundInterface;

final class ResourceNotFoundException extends \RuntimeException implements ResourceNotFoundInterface
{

}
