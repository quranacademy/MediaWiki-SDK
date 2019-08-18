<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\HttpClient;

use MediaWiki\HttpClient\CurlHttpClient;

class CurlHttpClientTest
{
    public function testCreate(): void
    {
        new CurlHttpClient();
    }
}
