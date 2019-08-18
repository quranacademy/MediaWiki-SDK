<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\Project;

use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Services\ServiceManager;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Tests\Stubs\ExampleProject;
use MediaWiki\Tests\TestCase;
use Mockery;

class ProjectTest extends TestCase
{
    public function testGetName(): void
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $this->assertEquals('foo', $project->getName());
    }

    public function testGetTitle(): void
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $this->assertEquals('Foo', $project->getTitle());
    }

    public function testGetDefaultLanguage(): void
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $this->assertEquals('en', $project->getDefaultLanguage());
    }

    public function testGetApiCollection(): void
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $this->assertEquals($apiCollection, $project->getApiCollection());
    }

    public function testAddApi(): void
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $enApi = $this->createApi();
        $ruApi = $this->createApi();

        $project->addApi('en', $enApi);
        $project->addApi('ru', $ruApi);

        $this->assertEquals($enApi, $project->getApiCollection()->get('en'));
        $this->assertEquals($ruApi, $project->getApiCollection()->get('ru'));
    }

    public function testApi(): void
    {
        $enApi = $this->createApi();
        $ruApi = $this->createApi();

        $apiCollection = new ApiCollection();

        $apiCollection->add('en', $enApi);
        $apiCollection->add('ru', $ruApi);

        $serviceManager = new ServiceManager($apiCollection);

        $project = new ExampleProject($apiCollection, $serviceManager);

        $this->assertEquals($enApi, $project->api('en'));
        $this->assertEquals($ruApi, $project->api('ru'));
    }

    protected function createApi(): Api
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        return new Api($url, $client, $storage);
    }
}
