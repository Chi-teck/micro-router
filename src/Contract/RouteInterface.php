<?php


declare(strict_types=1);

namespace MicroRouter\Contract;

interface RouteInterface
{
    public function getMethods(): array;

    public function getPath(): string;

    public function getHandler(): mixed;

    public function getRequirements(): array;

    public function getDefaults(): array;

    public function getOptions(): array;

    public function withPath(string $path): self;
}
