<?php

declare(strict_types=1);

namespace MediaWiki\ApiHelpers;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\ApiInterface;

class ApiHelper
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
    protected function api(string $language): ApiInterface
    {
        return $this->api->get($language);
    }
}
