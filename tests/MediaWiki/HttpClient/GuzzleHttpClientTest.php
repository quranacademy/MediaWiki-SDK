<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\HttpClient;

use MediaWiki\HttpClient\GuzzleHttpClient;

class GuzzleHttpClientTest
{
    public function testCreate(): void
    {
        new GuzzleHttpClient();
    }
}
