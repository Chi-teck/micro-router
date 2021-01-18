<?php
declare(strict_types=1);

namespace MicroRouter\Tests\Cache;

use Psr\SimpleCache\CacheInterface;

final class MemoryCache implements CacheInterface
{
    private array $data = [];

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null): iterable
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return isset($this->data[$key]);
    }
}
