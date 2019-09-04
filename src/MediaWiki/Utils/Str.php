<?php

declare(strict_types=1);

namespace MediaWiki\Utils;

class Str
{
    /**
     * Convert a value to studly caps case.
     *
     * @param string $string
     *
     * @return string
     */
    public static function pascalCase(string $string): string
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));

        return str_replace(' ', '', $string);
    }
}
