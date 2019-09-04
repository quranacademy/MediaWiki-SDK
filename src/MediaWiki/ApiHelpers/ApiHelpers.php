<?php

declare(strict_types=1);

namespace MediaWiki\ApiHelpers;

use MediaWiki\Api\ApiCollection;

class ApiHelpers
{
    /**
     * @var ApiCollection
     */
    protected $api;

    /**
     * @var Pages
     */
    protected $pagesHelper;

    /**
     * @var Namespaces
     */
    protected $namespacesHelper;

    /**
     * @var SiteInfo
     */
    protected $siteInfo;

    /**
     * Constructor.
     *
     * @param ApiCollection $api
     */
    public function __construct(ApiCollection $api)
    {
        $this->api = $api;
    }

    public function pages(): Pages
    {
        if ($this->pagesHelper === null) {
            $this->pagesHelper = new Pages($this->api);
        }

        return $this->pagesHelper;
    }

    public function namespaces(): Namespaces
    {
        if ($this->namespacesHelper === null) {
            $this->namespacesHelper = new Namespaces($this->api);
        }

        return $this->namespacesHelper;
    }

    public function siteInfo(): SiteInfo
    {
        if ($this->siteInfo === null) {
            $this->siteInfo = new SiteInfo($this->api);
        }

        return $this->siteInfo;
    }
}
