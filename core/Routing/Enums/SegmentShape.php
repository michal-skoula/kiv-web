<?php

namespace Core\Routing\Enums;

enum SegmentShape
{
    /**
     * Non-wildcard segment that stays constant
     */
    case CONSTANT;

    /**
     * Required segment that must not be missing.
     */
    case REQUIRED;

    /**
     * A segment that doesn't have to exist. In a correctly defined
     * route these will only ever be trailing and not in the middle.
     */
    case NULLABLE;

    /**
     * A segment that can have multiple values, separated by commas.
     */
    case LIST;

    /**
     * The prefix that identifies a wildcard segment, ex. `{...values}`
     *
     * @return ?string
     */
    public function identifier(): ?string
    {
        return match ($this) {
            self::CONSTANT => null,
            self::REQUIRED => null,
            self::NULLABLE => '?',
            self::LIST => '...',
        };
    }
}
