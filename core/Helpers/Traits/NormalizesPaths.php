<?php

namespace Core\Helpers\Traits;

trait NormalizesPaths
{
    /**
     * Normalizes a path so that it always starts with a `/` and never ends with one.
     *
     * - `/path/to/file` -> `/path/to/file`
     *
     * - `path/to/file/` -> `/path/to/file`
     *
     * - `my/file.php/` -> `/my/file.php`
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }
}