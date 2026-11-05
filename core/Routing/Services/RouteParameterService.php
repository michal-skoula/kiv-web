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
     *
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

            if(! $label->shape->isWildcard() || ! $label->type->isWildcard()) {
                continue;
            }

            // This means the segment is NULLABLE
            if($segment === null) {
                $parameters[$label->name] = new RouteParameter(
                    value: null,
                    type: SegmentDataType::NONE
                );
                continue;
            }

            $type = $label->type;
            if(! $type->matches($segment)) {
                throw new RoutingException("Segment {$label->name} does not match expected type");
            }

            $parameters[$label->name] = new RouteParameter(
                value: $type->castToSame($segment),
                type: $type
            );
        }

        return $parameters;
    }


}