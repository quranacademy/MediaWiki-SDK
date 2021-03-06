<?php

declare(strict_types=1);

namespace MediaWiki\HttpClient;

class CurlHttpClient implements HttpClientInterface
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $cookies;

    /**
     * Constructor.
     *
     * @param array $cookies
     * @param array $headers
     */
    public function __construct(array $cookies = [], array $headers = [])
    {
        $this->headers = $headers;
        $this->cookies = $cookies;
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
        $curlHandle = curl_init();

        $curlOptions = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_POSTFIELDS     => $parameters,
            CURLOPT_HTTPHEADER     => $this->buildRequestHeaders($headers, $cookies),
        ];

        curl_setopt_array($curlHandle, $curlOptions);

        $response = curl_exec($curlHandle);
        $response = $this->parseResponse($response, $curlHandle);

        if (array_key_exists('Set-Cookie', $response['headers'])) {
            $newCookies = $response['headers']['Set-Cookie'];

            unset($newCookies['domain'], $newCookies['path'], $newCookies['expires'], $newCookies['SameSite']);

            $this->cookies = array_merge($this->cookies, $newCookies);
        }

        curl_close($curlHandle);

        return $response['body'];
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
        return $this->cookies;
    }

    /**
     * @param array $headers
     * @param array $cookies
     *
     * @return array
     */
    private function buildRequestHeaders($headers, $cookies): array
    {
        $headers = array_merge($this->headers, $headers);

        $cookies = array_merge($this->cookies, $cookies);

        if (count($cookies) > 0) {
            $headers['Cookie'] = $this->buildCookieString($cookies);
        }

        $result = [];

        foreach ($headers as $name => $value) {
            $result[] = "{$name}: {$value}";
        }

        return $result;
    }

    /**
     * @param array $cookies
     *
     * @return string
     */
    private function buildCookieString($cookies): string
    {
        $strings = [];

        foreach ($cookies as $name => $value) {
            $strings[] = "{$name}={$value}";
        }

        return implode('; ', $strings);
    }

    /**
     * @param string $response
     * @param resource $curlHandle cURL handle
     *
     * @return array
     */
    private function parseResponse(string $response, $curlHandle): array
    {
        $info = curl_getinfo($curlHandle);
        $headerSize = $info['header_size'];

        $headersString = trim(substr($response, 0, $headerSize));
        $headers = $this->parseHttpHeaders($headersString);

        $body = trim(substr($response, $headerSize));

        return [
            'headers' => $headers,
            'body' => $body,
        ];
    }

    /**
     * @param string $headersString
     *
     * @return array
     */
    private function parseHttpHeaders($headersString): array
    {
        $result = [];

        $lines = explode("\r\n", $headersString);

        foreach ($lines as $line) {
            if (strpos($line, ': ') === false) {
                continue;
            }

            [$name, $value] = explode(': ', $line);

            if ($name === 'Set-Cookie') {
                if ( ! array_key_exists('Set-Cookie', $result)) {
                    $result['Set-Cookie'] = [];
                }

                $result['Set-Cookie'] = array_merge($result['Set-Cookie'], $this->parseCookie($value));
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $cookieString
     *
     * @return array
     */
    private function parseCookie($cookieString): array
    {
        $result = [];

        $lines = explode('; ', $cookieString);

        foreach ($lines as $line) {
            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line);

            $result[$name] = $value;
        }

        return $result;
    }
}
