<?php
declare(strict_types=1);

namespace MicroRouter;

use MicroRouter\Contract\RouteInterface;

final class Route implements RouteInterface, \Stringable
{
    public function __construct(
        private array $methods,
        private string $path,
        private mixed $handler,
        private array $requirements = [],
        private array $defaults = [],
        private array $options = [],
    ) {
        \sort($this->methods);
    }

    /**
     * Route factory.
     *
     * Usage:
     * @code
     *   Route::create('GET|POST', '/example/{id=123:\d+}', 'ExampleHandler');
     * @endcode
     */
    public static function create(
        string $methods,
        string $path,
        mixed $handler,
        array $requirements = [],
        array $defaults = [],
        array $options = [],
    ): self {
        $methods = \explode('|', $methods);
        $path = \preg_replace_callback(
            '#{(.+?)}#',
            static function (array $matches) use (&$requirements, &$defaults): string {
                [$name, $pattern] = \array_pad(\explode(':', $matches[1]), 2, null);
                [$name, $value] = \array_pad(\explode('=', $name), 2, null);
                if ($value !== null) {
                    $defaults[$name] = $value;
                }
                if ($pattern !== null) {
                    $requirements[$name] = $pattern;
                }
                // Return pure name without pattern and default value.
                return '{' . $name . '}';
            },
            $path,
        );
        return new self($methods, $path, $handler, $requirements, $defaults, $options);
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function withPath(string $path): self
    {
        $route = clone $this;
        $route->path = $path;
        return $route;
    }

    public function __toString(): string
    {
        return \str_replace(
            ':' . self::class . ':private',
            '',
            \print_r($this, true),
        );
    }
}
