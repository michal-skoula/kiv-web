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
        return explode('/', trim($uri, '/'));
    }
}