<?php

declare(strict_types=1);

namespace MediaWiki\ApiHelpers;

class SiteInfo extends ApiHelper
{
    /**
     * @param string $language
     *
     * @return string
     */
    public function getVersion(string $language): string
    {
        $response = $this->api($language)->query([
            'meta' => 'siteinfo',
            'continue' => '',
        ]);

        $segments = explode(' ', $response['query']['general']['generator']);

        return $segments[1];
    }
}
