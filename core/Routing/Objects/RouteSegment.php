<?php

namespace Core\Routing\Objects;

use Core\Routing\Enums\SegmentDataType;
use Core\Routing\Enums\SegmentShape;

/**
 * Data Object containing information about one segment of a given Route.
 */
final readonly class RouteSegment
{
    /**
     * The actual text of the segment, stripped of all identifiers.
     *
     * This value is what the controller will be passed if the route resolves.
     *
     * Example: `{?identifier%i}` becomes `identifier`.
     *
     * Example: `{...users%s}` becomes `users`
     *
     * @var string
     */
    public string $name;

    /**
     * What type of segment this is.
     *
     * SegmentType determines how this particular segment
     * will be handled when mapped onto the URI.
     *
     * @var SegmentShape
     */
    public SegmentShape $shape;

    /**
     * What kind of data type this is.
     *
     * Whether the segment of the actual URI will be treated as a string, int, etc.
     *
     * @var SegmentDataType
     */
    public SegmentDataType $type;


    public function __construct(
        string          $segment,
        SegmentShape    $segmentType,
        SegmentDataType $dataType,
    ) {
        $this->name = $segment;
        $this->shape = $segmentType;
        $this->type = $dataType;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}