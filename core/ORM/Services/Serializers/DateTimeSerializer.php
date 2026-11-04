<?php

namespace Core\ORM\Services\Serializers;

use Core\ORM\Contracts\BuiltInSerializer;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeSerializer implements BuiltInSerializer
{
    /**
     * @param DateTimeImmutable $value
     */
    public static function serialize(mixed $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function deserialize(int|string $value, string $type): DateTimeImmutable
    {
        return new $type($value);
    }
}
