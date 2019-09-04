<?php

declare(strict_types=1);

namespace MediaWiki\Storage;

interface StorageInterface
{
    /**
     * Retrieve an item from the storage by key.
     *
     * @param string|array $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Store an item in the storage for a given number of minutes.
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     */
    public function put(string $key, $value, int $minutes): void;

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    public function increment(string $key, int $value = 1): int;

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param int $value
     *
     * @return int
     */
    public function decrement(string $key, int $value = 1): int;

    /**
     * Store an item in the storage indefinitely.
     *
     * @param string $key
     * @param mixed $value
     */
    public function forever(string $key, $value): void;

    /**
     * Remove an item from the storage.
     *
     * @param string $key
     * 
     * @return bool
     */
    public function forget(string $key): bool ;

    /**
     * Remove all items from the storage.
     */
    public function flush(): void;
}
