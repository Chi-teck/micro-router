<?php
declare(strict_types=1);

namespace MicroRouter\Contract\Exception;

interface MethodNotAllowedInterface extends MatcherExceptionInterface
{
    public function getAllowedMethods(): array;
}
