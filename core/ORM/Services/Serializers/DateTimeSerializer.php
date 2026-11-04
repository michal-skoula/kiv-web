<?php

namespace Core\ORM\Services\Serializers;

use Core\Exceptions\ORMMappingException;
use Core\ORM\Contracts\Serializer;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeSerializer implements Serializer
{
    /**
     * @param DateTimeImmutable $value
     */
    public static function serialize(mixed $value): string
    {
        return $value->format(DateTimeInterface::ATOM);
    }

    /**
     * @throws ORMMappingException
     */
    public static function deserialize(int|string $value, string $class): DateTimeImmutable
    {
        try {
            return new $class($value);
        } catch(DateMalformedStringException $e) {
            throw ORMMappingException::from($e, "Cannot parse column value as a date: {$e->getMessage()}");
        }
    }
}
