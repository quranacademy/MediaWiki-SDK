<?php

declare(strict_types=1);

namespace MediaWiki\HttpClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;

class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param array $cookies
     * @param array $headers
     */
    public function __construct(array $cookies = [], array $headers = [])
    {
        $cookieJar = new CookieJar(false, $cookies);

        $clientOptions = [
            'cookies' => $cookieJar,
            'headers' => $headers,
        ];

        $this->client = new GuzzleClient($clientOptions);
    }

    /**
     * Makes a HTTP request to the specified URL with the specified parameters.
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @param array $cookies
     *
     * @return string
     */
    public function request(string $method, string $url, array $parameters = [], array $headers = [], array $cookies = []): string
    {
        $options = [
            'form_params' => $parameters,
            'headers' => $headers,
        ];

        if ($cookies !== []) {
            $options['cookies'] = new CookieJar(false, $cookies);
        }

        $response = $this->client->request($method, $url, $options);

        return (string) $response->getBody(true);
    }

    /**
     * Makes a GET HTTP request to the specified URL.
     *
     * @param  string $url
     * @param  array $parameters
     * @param  array $headers
     * @param  array $cookies
     *
     * @return string
     */
    public function get(string $url, array $parameters = [], array $headers = [], array $cookies = []): string
    {
        return $this->request('GET', $url, $parameters);
    }

    /**
     * Makes a POST HTTP request to the specified URL.
     *
     * @param  string $url
     * @param  array $parameters
     * @param  array $headers
     * @param  array $cookies
     *
     * @return string
     */
    public function post(string $url, array $parameters = [], array $headers = [], array $cookies = []): string
    {
        return $this->request('POST', $url, $parameters);
    }

    /**
     * Returns received cookies.
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->client->getConfig('cookies')->toArray();
    }
}
