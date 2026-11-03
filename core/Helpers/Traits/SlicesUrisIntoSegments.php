<?php

namespace Core\Helpers\Traits;

trait SlicesUrisIntoSegments
{
    /**
     * Slices a non-normalized URI into segments.
     *
     * Example: `path/to/page/` -> `/path/to/page`
     *
     * @return list<string>
     */
    protected static function getUriSegments(string $uri): array
    {
        $withoutParams = parse_url($uri, PHP_URL_PATH);
        $trimmed = trim($withoutParams, '/');
        return explode('/', $trimmed);
    }
}