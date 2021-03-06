<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\Api;

use LogicException;
use MediaWiki\Api\Api;
use MediaWiki\Api\Exceptions\AccessDeniedException;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Tests\TestCase;
use Mockery;

class ApiTest extends TestCase
{
    public function testConstructor(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        new Api($url, $httpClient, $storage);
    }

    public function testGetUrl(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $httpClient, $storage);

        $this->assertEquals($url, $api->getUrl());
    }

    public function testGetHttpClient(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $httpClient, $storage);

        $this->assertEquals($httpClient, $api->getHttpClient());
    }

    public function testGetStorage(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $httpClient, $storage);

        $this->assertEquals($storage, $api->getStorage());
    }

    public function testQueryLogging(): void
    {
        $method = 'GET';
        $url = 'https://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];

        $parameters1 = ['action' => 'query'];
        $parameters2 = ['action' => 'query'];

        $expectedParameters1 = array_merge($defaultParameters, $parameters1);
        $expectedParameters2 = array_merge($defaultParameters, $parameters1);

        $expectedResponse1 = ['foo' => 'bar'];
        $expectedResponse2 = ['baz' => 'qux'];

        $headers = [];
        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);

        $arguments1 = [$method, $url, $expectedParameters1, $headers, $cookies];
        $arguments2 = [$method, $url, $expectedParameters2, $headers, $cookies];

        $httpClient->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));
        $httpClient->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));
        $httpClient->shouldReceive('request')->once()->withArgs($arguments2)->andReturn(json_encode($expectedResponse2));
        $httpClient->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $this->assertEquals([], $api->getQueryLog());

        $api->request($method, $parameters1);

        $this->assertEquals([], $api->getQueryLog());

        $api->enableQueryLog();

        $api->request($method, $parameters1);
        $api->request($method, $parameters2);

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters1,
                'response' => $expectedResponse1,
            ],
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog());

        $api->disableQueryLog();

        $api->request($method, $parameters1);

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters1,
                'headers' => $headers,
                'cookies' => $cookies,
                'response' => $expectedResponse1,
            ],
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'headers' => $headers,
                'cookies' => $cookies,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(['method', 'parameters', 'headers', 'cookies', 'response']));

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(null, 1));

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(['method', 'parameters', 'response'], 1));
    }

    /**
     * TODO: test method with string query (foo=bar&baz=qux).
     */
    public function testRequest(): void
    {
        $method = 'GET';
        $url = 'https://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];
        $parameters = ['action' => 'query'];

        $expectedResponse = ['foo' => 'bar'];

        $headers = [];
        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);

        $expectedParameters = array_merge($defaultParameters, $parameters);

        $arguments = [$method, $url, $expectedParameters, $headers, $cookies];

        $httpClient->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $response = $api->request($method, $parameters);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @expectedException LogicException
     */
    public function testRequestWithNotAllowedMethod(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $parameters = ['action' => 'query'];

        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $api->request('PUT', $parameters);
    }

    /**
     * @expectedException LogicException
     */
    public function testRequestDecodeNotJson(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $parameters = ['action' => 'query', 'format' => 'xml'];

        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $api->request('GET', $parameters);
    }

    /**
     * TODO: test login() method with invalid arguments.
     */
    public function testLogin(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $username = 'John@FooBot';
        $password = 'pri9l1fl1j315hmp3okbnqspqcgaue1t';
        $domain = null;

        $token = '21fb442aac84673468b66d2b46cf76c559299384+\\';

        $headers = [];
        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);

        $expectedParameters = [
            'action' => 'query',
            'meta' => 'tokens',
            'type' => 'login',
            'format' => 'json',
        ];

        $expectedResponse = [
            'query' => [
                'tokens' => [
                    'logintoken' => $token,
                ],
            ],
        ];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $httpClient->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $expectedParameters = [
            'action' => 'login',
            'lgname' => $username,
            'lgpassword' => $password,
            'lgdomain' => $domain,
            'lgtoken' => $token,
            'format' => 'json',
        ];

        $expectedResponse = [
            'login' => [
                'result' => 'Success',
            ],
        ];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $httpClient->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $receivedCookies = [
            'foo' => 'bar',
        ];

        $httpClient->shouldReceive('getCookies')->once()->andReturn($receivedCookies);

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);
        $storage->shouldReceive('forever')->once()->with($key, $receivedCookies)->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $api->login($username, $password);
    }

    /**
     * TODO: test method with string query (foo=bar&baz=qux).
     */
    public function testQuery(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $headers = [];
        $cookies = [];

        $expectedParameters = [
            'action' => 'query',
            'format' => 'json',
            'titles' => 'Foo',
        ];

        $expectedResponse = ['response' => 'Bar'];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $httpClient = Mockery::mock(HttpClientInterface::class);

        $httpClient->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $api->query(['titles' => 'Foo']);
    }

    /**
     * @expectedException LogicException
     */
    public function testQueryWithInvalidAction(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $cookies = [];

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $httpClient, $storage);

        $api->query(['action' => 'parse']);
    }

    public function testAccessDenied(): void
    {
        $url = 'https://wikipedia.org/w/api.php';

        $parameters = [
            'query' => 'query',
            'titles' => 'Page',
            'prop' => 'info|links',
        ];

        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $httpClient->shouldReceive('request')->once()->with('POST', $url, array_merge(['format' => 'json'], $parameters), [], [])->andReturn(json_encode([
            'error' => [
                'code' => 'readapidenied',
                'info' => 'You need read permission to use this module',
                '*' => 'See https://ru.holyquran.wiki/api.php for API usage',
            ],
        ]));

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $httpClient, $storage);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You need read permission to use this module');

        $api->request('POST', $parameters);
    }
}
