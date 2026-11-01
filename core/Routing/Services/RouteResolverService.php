<?php

namespace Core\Routing\Services;

use Core\Facades\App;
use Core\Routing\Enums\SegmentShape;
use Core\Routing\Objects\Request;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteSegment;

/**
 * Service used for resolving routes.
 *
 * Algorithm:
 * 1. Check the static portion of the route
 */
final class RouteResolverService
{
    /**
     * @param Route[] $routes List of routes from which to try to resolve it.
     * @param Request $request The request object.
     */
    public function __construct(
        protected array $routes,
        protected Request $request
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
        $uriSegments = $this->getUriSegments();

        /**
         * @var bool $onlyNullSeg Whenever a nullable URI segment appears, all following
         * segments must also be nullable. Same logic applies as in PHP functions that
         * have parameters with default values, must also be at the end.
         */
        $onlyNullSeg = false;

        /**
         * @var bool $reachedLastItem Array segment types allow an arbitrary
         * number of segments to follow. As such, no other type of segment
         * can be after it, as mapping by index no longer works by that
         * point and therefore it has to be the last segment of the URI.
         */
        $reachedLastItem = false;

        $routeSegments = $route->segments;
        for($i = 0; $i < count($routeSegments); $i++)
        {
            $uriSeg = $uriSegments[$i] ?? null;
            $routeSeg = $routeSegments[$i];

            if(! $this->isSegmentLegal($uriSeg, $routeSeg,$onlyNullSeg, $reachedLastItem)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decides whether a segment is legal from a set of rules.
     *
     * @param ?string $uriSeg Uri segment or null if there aren't any at this point.
     * @param RouteSegment $routeSeg Route segment from route definition.
     * @param bool $onlyNullSeg See comment on var definition
     * @param bool $reachedLastItem See comment on var definition
     *
     * @return bool
     */
    private function isSegmentLegal(?string $uriSeg, RouteSegment $routeSeg, bool &$onlyNullSeg, bool &$reachedLastItem): bool
    {
        // This means we're AFTER the last item, which is not allowed.
        if($reachedLastItem) {
            return false;
        }

        // These conditions are always evaluated together.
        $isRequired = $uriSeg !== null && $onlyNullSeg === false;

        switch ($routeSeg->shape)
        {
            case SegmentShape::CONSTANT:
                return $isRequired && $routeSeg->name === $uriSeg;
            case SegmentShape::REQUIRED:
                return $isRequired;
            case SegmentShape::NULLABLE:
                $onlyNullSeg = true;
                return true;
            case SegmentShape::LIST:
                $reachedLastItem = true;
                return $isRequired;
            default:
                App::log()->error(
                    message: 'Unknown PathSegmentShape enum case: ' . json_encode($routeSeg->shape),
                    data: [
                        $routeSeg,
                        $this->request->toArray(),
                    ]
                );
                return false;
        }
    }

    /**
     * @return list<string>
     */
    private function getUriSegments(): array
    {
        return explode('/', trim($this->request->uri, '/'));
    }
}