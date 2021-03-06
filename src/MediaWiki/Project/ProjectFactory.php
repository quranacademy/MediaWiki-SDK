<?php

declare(strict_types=1);

namespace MediaWiki\Project;

use InvalidArgumentException;
use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\ApiHelpers\ApiHelpers;
use MediaWiki\Storage\StorageInterface;
use RuntimeException;

class ProjectFactory implements ProjectFactoryInterface
{
    /**
     * HTTP client prototype.
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * Storage prototype.
     *
     * @var HttpClientInterface
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param HttpClientInterface $httpClient
     * @param StorageInterface $storage
     */
    public function __construct(HttpClientInterface $httpClient, StorageInterface $storage)
    {
        $this->httpClient = $httpClient;
        $this->storage = $storage;
    }

    /**
     * @param array $apiUrls
     * @param string $projectClassName
     *
     * @return Project
     *
     * @throws InvalidArgumentException if project class name is not string
     * @throws RuntimeException if specified project class does not exist
     * @throws RuntimeException if specified project class is not MediaWiki\Project\Project or a subclass of it
     */
    public function createProject(array $apiUrls = [], string $projectClassName = Project::class): Project
    {
        if ( ! is_string($projectClassName)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 4 to be string, %s given', __METHOD__, gettype($projectClassName)));
        }

        if ( ! class_exists($projectClassName)) {
            throw new RuntimeException(sprintf('Class with name "%s" does not exist', $projectClassName));
        }

        if ($projectClassName !== Project::class && ! is_subclass_of($projectClassName, Project::class, true)) {
            throw new RuntimeException(sprintf('Project class name must be %s or a subclass of it, %s given', Project::class, $projectClassName));
        }

        $httpClient = clone $this->httpClient;
        $storage = clone $this->storage;

        $apiCollection = new ApiCollection();

        foreach ($apiUrls as $language => $url) {
            $api = new Api($url, $httpClient, $storage);

            $apiCollection->add($language, $api);
        }

        $apiHelpers = new ApiHelpers($apiCollection);

        return new $projectClassName($apiCollection, $apiHelpers);
    }
}
