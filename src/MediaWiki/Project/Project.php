<?php

declare(strict_types=1);

namespace MediaWiki\Project;

use LogicException;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\ApiInterface;
use MediaWiki\ApiHelpers\ApiHelpers;

class Project
{
    /**
     * @var ApiCollection
     */
    protected $api;

    /**
     * @var ApiHelpers
     */
    protected $apiHelpers;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Constructor.
     *
     * @param ApiCollection $api
     * @param ApiHelpers $apiHelpers
     */
    public function __construct(ApiCollection $api, ApiHelpers $apiHelpers)
    {
        $this->api = $api;
        $this->apiHelpers = $apiHelpers;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string
     */
    public function setDefaultLanguage($language): void
    {
        $this->defaultLanguage = $language;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    /**
     * @param string $language
     * @param ApiInterface $api
     */
    public function addApi($language, ApiInterface $api): void
    {
        $this->api->add($language, $api);
    }

    /**
     * @param string|null $language
     *
     * @return ApiInterface
     */
    public function api($language = null): ApiInterface
    {
        if ($language === null && $this->defaultLanguage === null) {
            throw new LogicException('Please, specify language of API or default language of project');
        }

        $language = $language === null ? $this->defaultLanguage : $language;

        return $this->api->get($language);
    }

    /**
     * @return ApiCollection
     */
    public function getApiCollection(): ApiCollection
    {
        return $this->api;
    }

    /**
     * @return ApiHelpers
     */
    public function helpers(): ApiHelpers
    {
        return $this->apiHelpers;
    }

    /**
     * @return array
     */
    public static function getApiUrls(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getApiUsernames(): array
    {
        return [];
    }
}
