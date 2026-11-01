<?php

namespace Core\Routing\Enums;

enum SegmentShape: string
{
    /**
     * Non-wildcard segment that stays constant
     */
    case CONSTANT = 'constant';

    /**
     * Required segment that must not be missing.
     */
    case REQUIRED = 'required';

    /**
     * A segment that doesn't have to exist. In a correctly defined
     * route these will only ever be trailing and not in the middle.
     */
    case NULLABLE = 'nullable';

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
        };
    }
}
