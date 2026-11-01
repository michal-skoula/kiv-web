<?php

namespace Core\Helpers\Facades;

final readonly class Str
{
    /**
     * Finds the first occurrence of a string between two values, or returns null if none was found.
     *
     * @param string $string
     * @param string $start
     * @param string $end
     *
     * @return string|null
     */
    public static function between(string $string, string $start, string $end): ?string
    {
        // `preg_quote()` is for escaping the special characters
        // `(.*?)` is match everything
        // `/s` is to handle newlines properly
        $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/s';

        if (preg_match($pattern, $string, $match) == 1) {
            return $match[1];
        }

        return null;
    }

    /**
     * Returns everything before the first occurrence of a value, or null if the value wasn't found.
     *
     * @param string $string
     * @param string $before
     *
     * @return string|null
     */
    public static function before(string $string, string $before): ?string
    {
        $position = strpos($string, $before);

        if ($before === '' || $position === false) {
            return null;
        }

        return substr($string, 0, $position);
    }

    /**
     * Returns everything after the first occurrence of a value, or null if the value wasn't found.
     *
     * @param string $string
     * @param string $after
     *
     * @return string|null
     */
    public static function after(string $string, string $after): ?string
    {
        $position = strpos($string, $after);

        if ($after === '' || $position === false) {
            return null;
        }

        return substr($string, $position + strlen($after));
    }
}