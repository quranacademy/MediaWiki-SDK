<?php

declare(strict_types=1);

namespace MediaWiki\Api;

use InvalidArgumentException;
use LogicException;
use MediaWiki\Api\Exceptions\AccessDeniedException;
use MediaWiki\Api\Exceptions\ApiException;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;
use RuntimeException;

class Api implements ApiInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    protected $cookies;

    /**
     * @var bool
     */
    protected $logQueries = false;

    /**
     * @var QueryLog
     */
    protected $queryLog;

    /**
     * @var array
     */
    protected $defaultParameters = [
        'format' => 'json',
    ];

    /**
     * Constructor.
     *
     * @param string $url
     * @param HttpClientInterface $httpClient
     * @param StorageInterface $storage
     */
    public function __construct(string $url, HttpClientInterface $httpClient, StorageInterface $storage)
    {
        $this->setUrl($url);

        $this->queryLog = new QueryLog();

        $this->httpClient = $httpClient;
        $this->storage = $storage;

        $key = sprintf('%s.cookies', $this->url);

        $this->cookies = $this->storage->get($key, []);
    }

    /**
     * @param string $url
     *
     * @throws InvalidArgumentException if API URL is not string
     * @throws RuntimeException if API address is not valid URL
     */
    protected function setUrl(string $url): void
    {
        if ( ! is_string($url)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($url)));
        }

        if ( ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(sprintf('API address must must be a valid URL (%s)', $url));
        }

        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Enables query logging.
     */
    public function enableQueryLog(): void
    {
        $this->logQueries = true;
    }

    /**
     * Disables query logging.
     */
    public function disableQueryLog(): void
    {
        $this->logQueries = false;
    }

    /**
     * Returns query log.
     *
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getQueryLog(?array $fields = null, ?int $count = null): array
    {
        return $this->queryLog->getLog($fields, $count);
    }

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
     * @throws RuntimeException if response is not valid JSON
     * @throws AccessDeniedException if access to API or section denied (e.g., unauthorized request)
     */
    public function request(string $method, array $parameters = [], array $headers = [], bool $decode = true)
    {
        if ( ! $this->isMethodAllowed($method)) {
            $allowedMethods = implode(', ', $this->getAllowedRequestMethods());

            throw new LogicException(sprintf('Method "%s" is not allowed. Allowed methods: %s', $method, $allowedMethods));
        }

        if (is_string($parameters)) {
            parse_str($parameters, $result);

            $parameters = $result;
        }

        $parameters = array_merge($this->getDefaultParameters(), $parameters);

        if ($decode && (strtolower($parameters['format']) !== 'json')) {
            throw new LogicException('Only JSON can be decoded. Specify JSON format or disable decoding');
        }

        if ($this->logQueries) {
            $this->queryLog->logQuery($method, $parameters, $headers, $this->cookies);
        }

        $response = $this->httpClient->request($method, $this->url, $parameters, $headers, $this->cookies);

        if ($decode) {
            $response = $this->decodeResponse($response);
        }

        if ($this->logQueries) {
            $this->queryLog->appendResponse($response);
        }

        return $response;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethodAllowed(string $method): bool
    {
        return in_array(strtoupper($method), $this->getAllowedRequestMethods(), true);
    }

    /**
     * @return array
     */
    public function getAllowedRequestMethods(): array
    {
        return ['GET', 'POST'];
    }

    /**
     * @param string $response
     *
     * @return array
     *
     * @throws RuntimeException if response is not valid JSON
     * @throws AccessDeniedException if access to API or section denied (e.g., unauthorized request)
     */
    protected function decodeResponse(string $response): array
    {
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('API response is not valid JSON (%s)', $this->url));
        }

        if (array_key_exists('error', $decodedResponse)) {
            $error = $decodedResponse['error'];

            if ($error['code'] === 'readapidenied') {
                throw new AccessDeniedException($error['info']);
            }
        }

        return $decodedResponse;
    }

    /**
     * @param array|string $parameters
     * @param bool $decode
     *
     * @return array|string
     *
     * @throws LogicException if action specified and not equals "query"
     * @throws LogicException if request method is not allowed
     * @throws LogicException if response decoding enabled and response type is not JSON
     * @throws RuntimeException if response is not valid JSON
     * @throws AccessDeniedException if access to API or section denied (e.g., unauthorized request)
     */
    public function query($parameters, bool $decode = true)
    {
        if (is_string($parameters)) {
            parse_str($parameters, $result);

            $parameters = $result;
        }

        if (array_key_exists('action', $parameters) && strtolower($parameters['action']) !== 'query') {
            throw new LogicException('Invalid action. Omit action parameter or use request() method');
        }

        $parameters = array_merge(['action' => 'query'], $parameters);

        return $this->request('POST', $parameters, [], $decode);
    }

    /**
     * @param array $parameters
     *
     * @return Api
     */
    public function setDefaultParameters(array $parameters): ApiInterface
    {
        $this->defaultParameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return $this->defaultParameters;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string|null $domain
     *
     * @throws ApiException
     */
    public function login(string $username, string $password, ?string $domain = null): void
    {
        if ($username === '') {
            throw new RuntimeException(sprintf('Username can not be empty (%s)', $this->url));
        }

        if ($password === '') {
            throw new RuntimeException(sprintf('Password can not be empty (%s)', $this->url));
        }

        $data = [
            'action' => 'query',
            'meta' => 'tokens',
            'type' => 'login',
        ];

        $response = $this->request('POST', $data);

        $data = [
            'action' => 'login',
            'lgname' => $username,
            'lgpassword' => $password,
            'lgdomain' => $domain,
            'lgtoken' => $response['query']['tokens']['logintoken'],
        ];

        $response = $this->request('POST', $data);

        if ($response['login']['result'] === 'Success') {
            $this->cookies = $this->httpClient->getCookies();

            $key = sprintf('%s.cookies', $this->url);

            $this->storage->forever($key, $this->cookies);

            return;
        }

        if ($response['login']['result'] === 'Failed') {
            $exceptionMessage = $response['login']['result']."\n \n".$response['login']['reason'];
        } else {
            $exceptionMessage = $response['login']['result'];
        }

        throw new ApiException($exceptionMessage);
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->cookies !== [];
    }

    /**
     * @return bool
     *
     * @throws LogicException if request method is not allowed
     * @throws LogicException if response decoding enabled and response type is not JSON
     * @throws RuntimeException if response is not valid JSON
     * @throws AccessDeniedException if access to API or section denied (e.g., unauthorized request)
     */
    public function logout(): bool
    {
        $this->cookies = [];

        $key = sprintf('%s.cookies', $this->url);

        $this->storage->forget($key);

        $data = [
            'action' => 'logout',
        ];

        $response = $this->request('POST', $data);

        return $response === [];
    }
}
