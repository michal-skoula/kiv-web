<?php

namespace Core\Routing\Services;

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

            $segmentType = array_find(
                array: SegmentShape::cases(),
                callback: fn(SegmentShape $case) => str_starts_with($seg, $case->identifier() ?? self::NONE)
            ) ?? SegmentShape::REQUIRED;

            $dataType = array_find(
                array: SegmentDataType::cases(),
                callback: fn(SegmentDataType $case) => str_ends_with($seg, $case->identifier() ?? self::NONE)
            ) ?? SegmentDataType::STRING;

            $labeled[] =  new RouteSegment(
                segment: $seg,
                segmentType: $segmentType,
                dataType: $dataType,
            );
        }

        return $labeled;
    }

    private function isSegmentDynamic(string $segment): bool
    {
        return str_starts_with($segment, '{') && str_ends_with($segment, '}');
    }
}