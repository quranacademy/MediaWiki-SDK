# MediaWiki SDK

[![Build Status](https://travis-ci.org/quranacademy/MediaWiki-SDK.svg)](https://travis-ci.org/quranacademy/MediaWiki-SDK)
[![Total Downloads](https://poser.pugx.org/quranacademy/mediawiki-sdk/downloads)](https://packagist.org/packages/quranacademy/mediawiki-sdk)
[![Latest Stable Version](https://poser.pugx.org/quranacademy/mediawiki-sdk/v/stable)](https://packagist.org/packages/quranacademy/mediawiki-sdk)
[![License](https://poser.pugx.org/quranacademy/mediawiki-sdk/license)](https://packagist.org/packages/quranacademy/mediawiki-sdk)

MediaWiki SDK - библиотека для работы с API MediaWiki.

Минимальная версия MediaWiki: 1.27+.

## Содержание

- [Установка](#Установка)
- [Быстрый старт](#Быстрый-старт)
- [Авторизация](#Авторизация)
- [Выполнение запроса](#Выполнение-запроса)

## Установка

Для установки MediaWiki SDK выполните команду:

```
composer require quranacademy/mediawiki-sdk
```

## Быстрый старт

```php
use MediaWiki\Api\Api;
use MediaWiki\Api\Exceptions\ApiException;
use MediaWiki\Storage\FileStore;
use MediaWiki\HttpClient\CurlHttpClient;

$url = 'http://ru.example.com/api.php';

$httpClient = new CurlHttpClient();
$storage = new FileStore(__DIR__.'/storage/cache');

$api = new Api($url, $httpClient, $storage);
```

## Авторизация

```php
$username = 'John@FooBot';
$password = 'pri9l1fl1j315hmp3okbnqspqcgaue1t';

try {
    $api->login($username, $password);
} catch (ApiException $exception) {
    echo sprintf('MediaWiki API Error: ', $exception->getMessage());

    exit;
}

// bool(true)
var_dump($api->isLoggedIn());

// выход
$api->logout();
```

## Выполнение запроса

```php
$parameters = [
    'action' => 'query',
    'list' => 'allpages',
];

$response = $api->request('POST', $parameters);

// или

$parameters = [
    'list' => 'allpages',
];

$response = $api->query($parameters);

var_dump($response);
```

### Параметры метода `request`

- **method** - HTTP-метод (POST/GET)
- **parameters** - параметры запроса (опционально)
- **headers** - заголовки запроса (опционально)
- **decode** - декодирует запрос, если передан параметр `true`  (только json, опционально)

### Параметры метода `query`

- **parameters** - параметры запроса
- **decode** - декодирует запрос, если передан параметр `true`  (только json, опционально)
