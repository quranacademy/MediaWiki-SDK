<?php

declare(strict_types=1);

namespace MediaWiki\ApiHelpers;

use Psr\Log\InvalidArgumentException;

class Pages extends ApiHelper
{
    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @param string $language
     * @param string|null $continue
     * @param string|null $apcontinue
     * @param array $additionalParameters
     * 
     * @return array
     */
    public function getList(string $language, ?string $continue = null, ?string $apcontinue = null, array $additionalParameters = []): array
    {
        $parameters = [
            'list' => 'allpages',
        ];

        if ($continue !== null) {
            $parameters['continue'] = $continue;
            $parameters['apcontinue'] = $apcontinue;
        }

        $parameters = array_merge($parameters, $additionalParameters);

        $response = $this->api($language)->query($parameters);

        if (array_key_exists('continue', $response)) {
            $continue = $response['continue']['continue'];
            $apcontinue = $response['continue']['apcontinue'];
        } else {
            $continue = null;
            $apcontinue = null;
        }

        return [
            'list' => $response['query']['allpages'],
            'continue' => $continue,
            'apcontinue' => $apcontinue,
        ];
    }

    /**
     * @param string $language
     * @param string $title
     * @param array|string $properties
     * @param array $additionalParameters
     * 
     * @return array|null
     */
    public function getPageByTitle(string $language, string $title, array $properties = [], array $additionalParameters = []): ?array
    {
        if ($title === '') {
            throw new InvalidArgumentException(sprintf('Title must not be empty (%s)', $language));
        }

        $parameters = [
            'titles' => $title,
            'prop' => implode('|', $properties),
        ];

        $parameters = array_merge($parameters, $additionalParameters);

        $response = $this->api($language)->query($parameters);

        return array_shift($response['query']['pages']);
    }

    /**
     * @param string $language
     * @param int $pageId
     * @param array|string $properties
     * @param array $additionalParameters
     *
     * @return array|null
     */
    public function getPageById(string $language, int $pageId, array $properties = [], array $additionalParameters = []): ?array
    {
        if ($pageId === '') {
            throw new InvalidArgumentException(sprintf('Title must not be empty (%s)', $language));
        }

        $parameters = [
            'pageids' => $pageId,
            'prop' => implode('|', $properties),
        ];

        $parameters = array_merge($parameters, $additionalParameters);

        $response = $this->api($language)->query($parameters);

        return array_shift($response['query']['pages']);
    }

    /**
     * @param string $language
     * @param string $title
     * @param string $content
     * @param array $additionalParameters
     * 
     * @return array
     */
    public function savePage(string $language, string $title, string $content, array $additionalParameters = []): array
    {
        $token = $this->getCsrfToken($language);

        $parameters = [
            'action' => 'edit',
            'title' => $title,
            'text' => $content,
            'bot' => true,
            'nocreate' => true,
            'token' => $token,
        ];

        $parameters = array_merge($parameters, $additionalParameters);

        return $this->api($language)->request('POST', $parameters);
    }

    /**
     * @param string $language
     * @param string $title
     * @param array|string $properties
     * @param array $additionalParameters
     * 
     * @return array
     */
    public function parse(string $language, string $title, $properties = [], array $additionalParameters = []): array
    {
        $properties = is_array($properties) ? implode('|', $properties) : $properties;

        $parameters = [
            'action' => 'parse',
            'page' => $title,
            'disableeditsection' => true,
            'disablelimitreport' => true,
        ];

        if ($properties !== '') {
            $parameters['prop'] = $properties;
        }

        $parameters = array_merge($parameters, $additionalParameters);

        return $this->api($language)->request('POST', $parameters);
    }

    /**
     * @param string $language
     * 
     * @return string
     */
    protected function getCsrfToken(string $language): string
    {
        if ( ! array_key_exists($language, $this->tokens)) {
            $parameters = [
                'action' => 'query',
                'meta' => 'tokens',
                'type' => 'csrf',
            ];

            $response = $this->api($language)->request('POST', $parameters);

            $this->tokens[$language] = $response['query']['tokens']['csrftoken'];
        }

        return $this->tokens[$language];
    }
}
