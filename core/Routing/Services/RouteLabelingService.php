<?php

namespace Core\Routing\Services;

use Core\Helpers\Facades\Str;
use Core\Routing\Enums\SegmentDataType;
use Core\Routing\Enums\SegmentShape;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteSegment;


final readonly class RouteLabelingService
{
    private const string NONE = 'N/A';

    public function __construct(private Route $route) {}

    /**
     * @return list<RouteSegment>
     */
    public function label(): array
    {
        $labeled = [];
        $targetSegments = explode('/', $this->route->definition);

        foreach($targetSegments as $seg) {
            if(! $this->isSegmentDynamic($seg)) {
                $labeled[] = new RouteSegment(
                    segment: $seg,
                    segmentType: SegmentShape::CONSTANT,
                    dataType: SegmentDataType::NONE,
                );
                continue;
            }

            $seg = ltrim($seg, '{');
            $seg = rtrim($seg, '}');

            $labeled[] = $this->toRouteSegment($seg);
        }

        return $labeled;
    }

    /**
     * @param string $segment Segment with `{}` stripped off.
     *
     * @return RouteSegment
     */
    public function toRouteSegment(string $segment): RouteSegment
    {
        $segmentShape = array_find(
            array: SegmentShape::cases(),
            callback: fn(SegmentShape $case) => str_starts_with($segment, $case->identifier() ?? self::NONE)
        );

        $segmentType = array_find(
            array: SegmentDataType::cases(),
            callback: fn(SegmentDataType $case) => str_ends_with($segment, $case->identifier() ?? self::NONE)
        );

        $name = $segment;
        if ($segmentShape) {
            $name = Str::after($name, $segmentShape->identifier());
        }

        if ($segmentType) {
            $name = Str::before($name, $segmentType->identifier());
        }

        return new RouteSegment(
            segment: $name,
            segmentType: $segmentShape ?? SegmentShape::REQUIRED,
            dataType: $segmentType ?? SegmentDataType::STRING,
        );
    }

    private function isSegmentDynamic(string $segment): bool
    {
        return str_starts_with($segment, '{') && str_ends_with($segment, '}');
    }
}