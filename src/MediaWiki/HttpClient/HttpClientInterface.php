<?php

declare(strict_types=1);

namespace MediaWiki\HttpClient;

interface HttpClientInterface
{
    /**
     * Constructor.
     *
     * @param array $cookies
     * @param array $headers
     */
    public function __construct(array $cookies = [], array $headers = []);

    /**
     * Makes a HTTP request to the specified URL with the specified parameters.
     *
     * @param string $method
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function request(string $method, string $url, array $parameters = [], array $headers = [], array $cookies = []): string;

    /**
     * Makes a GET HTTP request to the specified URL.
     *
     * @param string $url
     * @param array $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function get(string $url, array $parameters = [], array $headers = [], array $cookies = []): string;

    /**
     * Makes a POST HTTP request to the specified URL.
     *
     * @param string $url
     * @param array $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function post(string $url, array $parameters = [], array $headers = [], array $cookies = []): string;

    /**
     * Returns received cookies.
     *
     * @return array
     */
    public function getCookies(): array;
}
