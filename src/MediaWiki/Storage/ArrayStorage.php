<?php

declare(strict_types=1);

namespace MediaWiki\Storage;

class ArrayStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $storage = [];

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
        $array = $this->getPayload($key);

        return $array['data'] ?? $default;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param string $key
     *
     * @return array
     */
    protected function getPayload($key): array
    {
        if ( ! array_key_exists($key, $this->storage)) {
            return ['data' => null, 'time' => null];
        }

        $contents = $this->storage[$key];

        $expire = substr($contents, 0, 10);

        if (time() >= $expire) {
            $this->forget($key);

            return ['data' => null, 'time' => null];
        }

        $data = unserialize(substr($contents, 10), [
            'allowed_classes' => false,
        ]);

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil(($expire - time()) / 60);

        return compact('data', 'time');
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
        $value = $this->expiration($minutes).serialize($value);

        $this->storage[$key] = $value;
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
        $raw = $this->getPayload($key);

        $int = ((int) $raw['data']) + $value;

        $this->put($key, $int, (int) $raw['time']);

        return $int;
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
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     */
    public function forever(string $key, $value): void
    {
        $this->put($key, $value, 0);
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
        unset($this->storage[$key]);

        return true;
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): void
    {
        $this->storage = [];
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param int $minutes
     * 
     * @return int
     */
    protected function expiration(int $minutes): int
    {
        $time = time() + ($minutes * 60);

        if ($minutes === 0 || $time > 9999999999) {
            return 9999999999;
        }

        return $time;
    }
}
