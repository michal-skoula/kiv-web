<?php

namespace Core\Routing\Enums;

enum SegmentDataType: string
{
    /**
     * For PathSegment::CONSTANT, as they have no value
     */
    case NONE = 'none';

    /**
     * Maps to a string data type.
     */
    case STRING = 'string';

    /**
     * Maps to an integer data type.
     */
    case INTEGER = 'integer';

    public function identifier(): ?string
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => '%s',
            self::INTEGER => '%i',
        };
    }
}
