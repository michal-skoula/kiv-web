<?php

namespace Core\Routing\Services;

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
     */
    public function resolveParameters(): array
    {
        /** @var array<string, RouteParameter> $parameters */
        $parameters = [];

        $segments = self::getUriSegments($this->uri);
        $labels = new RouteLabelingService($this->route)
            ->label();

        for($i = 0; $i < count($labels); $i++) {
            $lab = $labels[$i];
            $seg = $segments[$i] ?? null;

            // These aren't wildcards, just static segments.
            if($lab->shape === SegmentShape::CONSTANT || $lab->type === SegmentDataType::NONE) {
                continue;
            }

            // With nullable parameters a URI can have
            // fewer segments than the route has labels.
            if($seg === null) {
                continue;
            }

            $parameters[$lab->name] = new RouteParameter(
                value: $seg,
                type: $lab->type
            );
        }

        return $parameters;
    }


}