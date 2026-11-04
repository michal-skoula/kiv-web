<?php

namespace Core\Exceptions;

use ReflectionNamedType;
use Throwable;

class ORMMappingException extends ORMException
{
    public static function propertyDoesntExist(string $column): self
    {
        return new self("Column '$column' has no property to map to.");
    }

    public static function missingTypeDeclaration(string $property): self
    {
        return new self("Cannot cast property '$property' because it has no type declaration.");
    }

    public static function propertyNotAttributed(string $property): self
    {
        return new self("Model property '$property' must be explicitly marked with #[Property]");
    }
    public static function invalidType(ReflectionNamedType $expected, mixed $got): self
    {
        return new self("Cannot cast property '{$expected->getName()}' to '$got'");
    }

    public static function typeNotSupported(ReflectionNamedType $type, string $message): self
    {
        return new self("Cannot cast type '{$type->getName()}'. $message");
    }

    public static function propertyIsStatic(string $property): self
    {
        return new self("Model properties must not be static: '$property'");
    }

    public static function unionTypeNotSupported(): self
    {
        return new self("Model properties cannot use union types apart from nullable");
    }

    public static function intersectionTypeNotSupported(): self
    {
        return new self("Model properties cannot use intersection types");
    }

    public static function cannotCastType(mixed $value, ?Throwable $previous = null): self
    {
        $type =  get_debug_type($value);
        return new self(
            message: "Cannot cast property of '$type'. Make sure it " .
            'implements the ModelPropertySerializable interface.',
            previous: $previous,
        );
    }
}