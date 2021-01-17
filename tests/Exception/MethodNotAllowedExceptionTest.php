<?php
declare(strict_types=1);

namespace MicroRouter\Tests\Exception;

use MicroRouter\Exception\MethodNotAllowedException;
use PHPUnit\Framework\TestCase;

final class MethodNotAllowedExceptionTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $exception = new MethodNotAllowedException(
            allowedMethods: $allowed_methods = ['GET', 'POST'],
            message: $message = 'Test',
            code: $code = 0,
            previous: $previous = null,
        );

        self::assertSame($allowed_methods, $exception->getAllowedMethods());
        self::assertSame($message, $exception->getMessage());
        self::assertSame($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
