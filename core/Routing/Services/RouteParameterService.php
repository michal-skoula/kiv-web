<?php

namespace Core\Routing\Services;

use Core\Exceptions\RoutingException;
use Core\Helpers\Traits\SlicesUrisIntoSegments;
use Core\Routing\Enums\SegmentDataType;
use Core\Routing\Enums\SegmentShape;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteParameter;

final readonly class RouteParameterService
{
    use SlicesUrisIntoSegments;

    public function __construct(
        private Route $route,
        private string $uri,
    ) {}

    /**
     * @return array<string, RouteParameter>
     * @throws RoutingException
     */
    public function resolveParameters(): array
    {
        /** @var array<string, RouteParameter> $parameters */
        $parameters = [];

        $segments = self::getUriSegments($this->uri);
        $labels = new RouteLabelingService($this->route)
            ->label();

        for($i = 0; $i < count($labels); $i++) {
            $label = $labels[$i];
            $segment = $segments[$i] ?? null;

            // These aren't wildcards, just static segments.
            if($label->shape === SegmentShape::CONSTANT || $label->type === SegmentDataType::NONE) {
                continue;
            }

            // With nullable parameters a URI can have
            // fewer segments than the route has labels.
            if($segment === null) {
                continue;
            }

            $type = $label->type;
            if(! $type->typeMatches($segment)) {
                throw new RoutingException("Segment {$label->name} does not match expected type");
            }

            $parameters[$label->name] = new RouteParameter(
                value: $type->castToSameType($segment),
                type: $label->type
            );
        }

        return $parameters;
    }


}