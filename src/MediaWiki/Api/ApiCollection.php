<?php

declare(strict_types=1);

namespace MediaWiki\Api;

use InvalidArgumentException;

class ApiCollection
{
    /**
     * @var array
     */
    protected $api = [];

    /**
     * Constructor.
     * 
     * @param array $api
     *
     * @throws InvalidArgumentException if API collection is not array
     */
    public function __construct(array $api = [])
    {
        foreach ($api as $language => $instance) {
            $this->add($language, $instance);
        }
    }

    /**
     * @param string $language
     * @param ApiInterface $api
     */
    public function add(string $language, ApiInterface $api): void
    {
        $this->api[$language] = $api;
    }

    /**
     * @param string $language
     * 
     * @return ApiInterface
     *
     * @throws InvalidArgumentException if API wih specified language code does not exist
     */
    public function get(string $language): ApiInterface
    {
        if ($this->has($language)) {
            return $this->api[$language];
        }

        throw new InvalidArgumentException(sprintf('API with code "%s" not found', $language));
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->api;
    }

    /**
     * @param string $language
     * 
     * @return bool
     *
     * @throws InvalidArgumentException if language code is not string
     */
    public function has($language): bool
    {
        if ( ! is_string($language)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($language)));
        }

        return array_key_exists($language, $this->api);
    }

    /**
     * @return string[]
     */
    public function getLanguages(): array
    {
        return array_keys($this->api);
    }

    /**
     * Enables query logging for all APIs.
     *
     * @return ApiCollection
     */
    public function enableQueryLog(): self
    {
        foreach ($this->api as $language => $api) {
            $api->enableQueryLog();
        }

        return $this;
    }

    /**
     * Disables query logging for all APIs.
     *
     * @return ApiCollection
     */
    public function disableQueryLog(): self
    {
        foreach ($this->api as $language => $api) {
            $api->disableQueryLog();
        }

        return $this;
    }

    /**
     * Returns query logs from all APIs.
     *
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getQueryLog(array $fields = null, ?int $count = null): array
    {
        $log = [];

        foreach ($this->api as $language => $api) {
            $log[$language] = $api->getQueryLog($fields, $count);
        }

        return $log;
    }
}
