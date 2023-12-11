<?php

declare(strict_types=1);

namespace Auth0\WordPress\Utilities;

use function count;
use function is_string;
use function strlen;

final class Sanitize
{
    public static function alphanumeric(string | null $item, string $allowed = 'A-Za-z0-9 '): string | null
    {
        if ('' === $item) {
            return $item;
        }

        if (null === $item) {
            return $item;
        }

        return preg_replace('/[^' . $allowed . ']/', '', $item);
    }

    /**
     * @param array $array
     *
     * @return mixed[]
     */
    public static function arrayUnique(array $array): array
    {
        if ([] === $array) {
            return [];
        }

        return array_values(array_filter(array_unique(array_map('trim', $array))));
    }

    public static function boolean(string $string): ?string
    {
        $string = trim(sanitize_text_field($string));

        if ('' === $string) {
            return null;
        }

        if ('true' === $string) {
            return 'true';
        }

        if ('1' === $string) {
            return 'true';
        }

        return 'false';
    }

    public static function cookiePath(string $path): string
    {
        $path = trim(sanitize_text_field($path));
        $path = trim(str_replace(['../', './'], '', $path));
        $path = trim($path, "/ \t\n\r\0\x0B");

        if ('' !== $path) {
            return '/' . $path;
        }

        return $path;
    }

    public static function domain(string $path): ?string
    {
        $path = self::string($path);

        if (is_string($path) && '' === $path) {
            return null;
        }

        if (null === $path) {
            return null;
        }

        $scheme = parse_url($path, PHP_URL_SCHEME);

        if (null === $scheme) {
            return self::domain('http://' . $path);
        }

        $host = parse_url($path, PHP_URL_HOST);
        if (! is_string($host)) {
            return null;
        }

        if ('' === $host) {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) < 2) {
            return null;
        }

        $tld = end($parts);

        if (strlen($tld) < 2) {
            return null;
        }

        return $host;
    }

    public static function integer(string $string, int $max = 10, int $min = 0): ?int
    {
        $string = trim(sanitize_text_field($string));

        if ('' === $string) {
            return null;
        }

        if (! is_numeric($string)) {
            return null;
        }

        $int = (int) $string;

        if ($int < $min) {
            return 0;
        }

        if ($int > $max) {
            return $max;
        }

        return $int;
    }

    public static function string(string $string): ?string
    {
        $string = trim(sanitize_text_field($string));

        if ('' === $string) {
            return null;
        }

        return $string;
    }

    public static function textarea(string $string): ?string
    {
        $string = trim(sanitize_textarea_field($string));

        if ('' === $string) {
            return null;
        }

        return $string;
    }
}
