<?php

declare(strict_types=1);

namespace MediaWiki\Api;

use LogicException;
use MediaWiki\Api\Exceptions\ApiException;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;

interface ApiInterface
{
    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface;

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * Enables query logging.
     */
    public function enableQueryLog(): void;

    /**
     * Disables query logging.
     */
    public function disableQueryLog(): void;

    /**
     * Returns query log.
     *
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getQueryLog(?array $fields = null, ?int $count = null): array;

    /**
     * @param string $method HTTP method name
     * @param array|string $parameters
     * @param array $headers
     * @param bool $decode
     *
     * @return string|array
     *
     * @throws LogicException if request method is not allowed
     * @throws LogicException if response decoding enabled and response type is not JSON
     */
    public function request(string $method, array $parameters = [], array $headers = [], bool $decode = true);

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethodAllowed(string $method): bool;

    /**
     * @return array
     */
    public function getAllowedRequestMethods(): array;

    /**
     * @param array $parameters
     * @param bool $decode
     *
     * @return array|string
     *
     * @throws LogicException if action specified and not equals "query"
     */
    public function query($parameters, bool $decode = true);

    /**
     * @param array $parameters
     *
     * @return ApiInterface
     */
    public function setDefaultParameters(array $parameters): self;

    /**
     * @return array
     */
    public function getDefaultParameters(): array;

    /**
     * @param string $username
     * @param string $password
     * @param string|null $domain
     *
     * @throws ApiException
     */
    public function login(string $username, string $password, ?string $domain = null);

    /**
     * @return bool
     */
    public function isLoggedIn(): bool;

    /**
     * @return bool
     */
    public function logout(): bool;
}
