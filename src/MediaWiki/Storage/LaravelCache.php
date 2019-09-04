<?php

declare(strict_types=1);

namespace MediaWiki\Storage;

use Illuminate\Contracts\Cache\Repository;

class LaravelCache implements StorageInterface
{
    /**
     * The file cache directory.
     *
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param Repository $cache
     * @param string $prefix
     */
    public function __construct(Repository $cache, string $prefix = '')
    {
        $this->repository = $cache;
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->repository->get($this->prefix.$key, $default);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     */
    public function put(string $key, $value, int $minutes): void
    {
        $this->repository->put($this->prefix.$key, $value, $minutes);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    public function increment(string $key, int $value = 1): int
    {
        return $this->repository->increment($this->prefix . $key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    public function decrement(string $key, int $value = 1): int
    {
        return $this->repository->decrement($this->prefix . $key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     */
    public function forever(string $key, $value): void
    {
        $this->repository->forever($this->prefix.$key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget(string $key): bool
    {
        return $this->repository->forget($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): void
    {
        $this->repository->flush();
    }

    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
