<?php

declare(strict_types=1);

namespace MediaWiki\Helpers;

if ( ! function_exists('pascal_case')) {
    /**
     * Convert a value to studly caps case.
     *
     * @param string  $string
     * 
     * @return string
     */
    function pascal_case(string $string): string
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));

        return str_replace(' ', '', $string);
    }
}

if ( ! function_exists('camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param string  $string
     * 
     * @return string
     */
    function camel_case(string $string): string
    {
        return lcfirst(pascal_case($string));
    }
}

if ( ! function_exists('snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param string  $string
     * @param string  $delimiter
     * 
     * @return string
     */
    function snake_case(string $string, string $delimiter = '_'): string
    {
        if ( ! ctype_lower($string)) {
            $string = preg_replace('/\s+/u', '', $string);
            $string = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $string));
        }

        return $string;
    }
}

if ( ! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed   $default
     * 
     * @return mixed
     */
    function array_get(array $array, ?string $key, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
