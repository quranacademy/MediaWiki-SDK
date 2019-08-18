<?php

namespace MediaWiki\Tests\Stubs;

use MediaWiki\Project\Project;

class ExampleProject extends Project
{
    /**
     * @var string
     */
    protected $name = 'foo';

    /**
     * @var string
     */
    protected $title = 'Foo';

    /**
     * @var string
     */
    protected $defaultLanguage = 'en';

    /**
     * @return array
     */
    public static function getApiUrls(): array
    {
        return [
            'en' => 'https://en.wikipedia.org/w/api.php',
            'ru' => 'https://ru.wikipedia.org/w/api.php',
        ];
    }

    /**
     * @return array
     */
    public static function getApiUsernames(): array
    {
        return [
            'en' => 'FooBot',
            'ru' => 'FooBot',
        ];
    }
}
