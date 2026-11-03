<?php

namespace Core\Routing\Services;

use Core\Helpers\Traits\SlicesUrisIntoSegments;
use Core\Routing\Enums\SegmentDataType;
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
        protected array  $routes,
        protected string $uri,
    )
    {
    }

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

        // Easy check: if the URI is longer than the route segments, it's not the right one.
        if (count($uriSegments) > count($routeSegments)) {
            return false;
        }

        for ($i = 0; $i < count($routeSegments); $i++) {
            $uriSeg = $uriSegments[$i] ?? null;
            $routeSeg = $routeSegments[$i];

            if (!$this->isSegmentLegal($uriSeg, $routeSeg, $nullableOnlyNow)) {
                return false;
            }

            // Basically checks "are nulls allowed now?". Runs after the segment is checked for legality,
            // as otherwise we'd match routes that can be one item shorter than intended (off-by-one).
            if ($uriSeg === null && $nullableOnlyNow !== true) {
                return false;
            }

            if (! $this->isTypeLegal($routeSeg->type, $uriSeg)) {
                return false;
            }
        }

        // Passed all the checks, this is the correct route.
        return true;
    }

    /**
     * Checks whether the URI segment matches the expected type.
     *
     * **IMPORTANT:** Must run after {@see isSegmentLegal()}.
     *
     * @param SegmentDataType $type
     * @param string|null $segment
     *
     * @return bool
     */
    private function isTypeLegal(SegmentDataType $type, ?string $segment): bool
    {
        // Any non-wildcard type that passed isSegmentLegal also has a valid type.
        if(! $type->isWildcard()) {
            return true;
        }

        // Nullable segments always have type null so there's nothing to check.
        if($segment === null) {
            return true;
        }

        return $type->matches($segment);
    }

    /**
     * Decides whether a segment is legal from a set of rules.
     *
     * @param ?string $uriSeg Uri segment
     * @param RouteSegment $routeSeg Route segment from route definition.
     * @param bool $nullOnly See comment on var definition
     *
     * @return bool
     */
    private function isSegmentLegal(?string $uriSeg, RouteSegment $routeSeg, bool &$nullOnly): bool
    {
        return match($routeSeg->shape) {
            // A constant shape can have uriSeg be '', because that's exactly what web root is.
            // labeling service marks a '/' route as [CONSTANT, NONE], which this handles.
            SegmentShape::CONSTANT => $nullOnly === false && $uriSeg !== null && $routeSeg->name === $uriSeg,
            // $uriSeg !== '' exists because otherwise there would be no way to distinguish a top-level
            // required route (`/{post}`) and root (`/`), as it would just match the empty string as a
            // valid parameter for required, which it isn't.
            SegmentShape::REQUIRED => $nullOnly === false && $uriSeg !== '',
            // Assigns and returns at the same time
            SegmentShape::NULLABLE => $nullOnly = true,
        };
    }
}