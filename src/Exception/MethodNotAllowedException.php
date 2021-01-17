<?php
declare(strict_types=1);

namespace MicroRouter\Exception;

use MicroRouter\Contract\Exception\MethodNotAllowedInterface;

final class MethodNotAllowedException extends \RuntimeException implements MethodNotAllowedInterface
{
    public function __construct(
        private array $allowedMethods = [],
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * The 405 HTTP response must include an Allow header containing a list of
     * valid methods for the requested resource.
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.6
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
