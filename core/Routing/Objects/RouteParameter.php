<?php

namespace Core\Routing\Objects;

use Core\Routing\Enums\SegmentDataType;

final readonly class RouteParameter
{
    /**
     * @param mixed $value The value, cast to the type matching SegmentDataType.
     * @param SegmentDataType $type The type of the value.
     */
    public function __construct(
        public mixed $value,
        public SegmentDataType $type
    ) {}
}