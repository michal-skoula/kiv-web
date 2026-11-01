<?php

namespace Core\Routing\Enums;

enum SegmentDataType
{
    /**
     * For PathSegment::CONSTANT, as they have no value
     */
    case NONE;

    /**
     * Maps to a string data type.
     */
    case STRING;

    /**
     * Maps to an integer data type.
     */
    case INTEGER;

    public function identifier(): ?string
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => '%s',
            self::INTEGER => '%i',
        };
    }
}
