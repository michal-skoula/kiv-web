<?php

namespace Core\Routing\Services;

use Core\Helpers\Traits\SlicesUrisIntoSegments;
use Core\Routing\Enums\SegmentShape;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteSegment;

final class RouteResolverService
{
    use SlicesUrisIntoSegments;

    /**
     * @param Route[] $routes List of routes from which to try to resolve it.
     * @param string $uri The request URI.
     */
    public function __construct(
        protected array $routes,
        protected string $uri,
    ) {}

    /**
     * @return Route|null The resolved route, or null if none matched.
     */
    public function findRouteOrNull(): Route|null
    {
        return array_find(
            array: $this->routes,
            callback: fn(Route $route) => $this->isValidRoute($route)
        ) ?? null;
    }

    private function isValidRoute(Route $route): bool
    {
        /**
         * @var bool $nullableOnlyNow Whenever a nullable URI segment
         * first appears, all following segments must also be nullable.
         */
        $nullableOnlyNow = false;

        $uriSegments = self::getUriSegments($this->uri);
        $routeSegments = new RouteLabelingService($route)->label();

        if(count($uriSegments) > count($routeSegments)) {
            return false;
        }

//        // Special `/` app root case
//        if($this->isRoot($uriSegments, $routeSegments)) {
//            return true;
//        }

        for($i = 0; $i < count($routeSegments); $i++)
        {
            $uriSeg = $uriSegments[$i] ?? null;
            $routeSeg = $routeSegments[$i];

            if(! $this->isSegmentLegal($uriSeg, $routeSeg,$nullableOnlyNow)) {
                return false;
            }

            // There are no more route segments in the Route, but the URI continues,
            // that means this route isn't specific enough and isn't the right one.
            if($uriSeg === null && ! $nullableOnlyNow) {
                return false;
            }
        }

        // Passed the gauntlet of conditions, this is the correct route.
        return true;
    }

    private function isRoot(array $uriSegments, array $routeSegments): bool
    {
        if(count($uriSegments) !== 1 || count($routeSegments) !== 1) {
            return false;
        }

        if($uriSegments[0] !== '' || $routeSegments[0]->name !== '') {
            return false;
        }

        return true;
    }

    /**
     * Decides whether a segment is legal from a set of rules.
     *
     * @param ?string $uriSeg Uri segment
     * @param RouteSegment $routeSeg Route segment from route definition.
     * @param bool $onlyNullSeg See comment on var definition
     *
     * @return bool
     */
    private function isSegmentLegal(?string $uriSeg, RouteSegment $routeSeg, bool &$onlyNullSeg): bool
    {
        return match($routeSeg->shape) {
            SegmentShape::CONSTANT => $onlyNullSeg === false && $uriSeg && $routeSeg->name === $uriSeg,
            SegmentShape::REQUIRED => $onlyNullSeg === false,
            SegmentShape::NULLABLE => $onlyNullSeg = true, // assign and return at the same time
        };
    }
}