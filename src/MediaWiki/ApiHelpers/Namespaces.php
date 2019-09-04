<?php

declare(strict_types=1);

namespace MediaWiki\ApiHelpers;

class Namespaces extends ApiHelper
{
    /**
     * Retrieves list of namespaces.
     * 
     * @param string $language
     * 
     * @return array
     */
    public function getList($language): array
    {
        $parameters = [
            'meta' => 'siteinfo',
            'siprop'=> 'namespaces',
            'formatversion' => 2,
        ];

        $response = $this->api($language)->query($parameters);

        return $response['query']['namespaces'];
    }
}
