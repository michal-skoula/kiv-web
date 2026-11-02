<?php

namespace Core\Routing\Services;

use Core\Facades\App;
use Core\Routing\Enums\SegmentShape;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteSegment;

final class RouteResolverService
{
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

        $uriSegments = $this->getUriSegments();
        $routeSegments = new RouteLabelingService($route)->label();

        for($i = 0; $i < count($uriSegments); $i++)
        {
            $uriSeg = $uriSegments[$i];
            $routeSeg = $routeSegments[$i] ?? null;


            // There are no more route segments in the Route, but the URI continues,
            // that means this route isn't specific enough and isn't the right one.
            if($routeSeg === null) {
                return false;
            }

            if(! $this->isSegmentLegal($uriSeg, $routeSeg,$nullableOnlyNow)) {
                return false;
            }
        }

        // Passed the gauntlet of conditions, this is the correct route.
        return true;
    }

    /**
     * Decides whether a segment is legal from a set of rules.
     *
     * @param string $uriSeg Uri segment
     * @param RouteSegment $routeSeg Route segment from route definition.
     * @param bool $onlyNullSeg See comment on var definition
     *
     * @return bool
     */
    private function isSegmentLegal(string $uriSeg, RouteSegment $routeSeg, bool &$onlyNullSeg): bool
    {
        return match($routeSeg->shape) {
            SegmentShape::CONSTANT => $onlyNullSeg === false && $routeSeg->name === $uriSeg,
            SegmentShape::REQUIRED => $onlyNullSeg === false,
            SegmentShape::NULLABLE => $onlyNullSeg = true, // assign and return at the same time
        };
    }

    /**
     * @return list<string>
     */
    private function getUriSegments(): array
    {
        return explode('/', trim($this->uri, '/'));
    }
}