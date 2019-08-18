<?php

declare(strict_types=1);

namespace MediaWiki\Services;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\ApiInterface;

class Service
{
    /**
     * Constructor.
     *
     * @param ApiCollection $api
     */
    public function __construct(ApiCollection $api)
    {
        $this->api = $api;
    }

    /**
     * @param string $language
     *
     * @return ApiInterface
     */
    protected function api(string $language)
    {
        return $this->api->get($language);
    }
}
