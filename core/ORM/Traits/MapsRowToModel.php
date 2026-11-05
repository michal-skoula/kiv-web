<?php

namespace Core\ORM\Traits;

use Core\Exceptions\ORMMappingException;
use Core\ORM\Attributes\Column;
use Core\ORM\Model;
use Core\ORM\Services\PropertySerializationService;
use Error;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait MapsRowToModel
{

    /**
     * @param array<string, mixed> $tableRow
     *
     * @throws ORMMappingException
     */
    protected static function mapRowToModel(Model $model, array $tableRow): void
    {
        $reflector = new ReflectionClass($model);

        foreach($tableRow as $column => $value)
        {
            try {
                $property = $reflector->getProperty($column);
            } catch(ReflectionException) {
                throw ORMMappingException::propertyDoesntExist($column);
            }

            self::castAndSetOrFail($model, $property, $value);
        }
    }

    /**
     * @throws ORMMappingException
     */
    private static function castAndSetOrFail(Model $model, ReflectionProperty $property, mixed $value): void
    {
        if(! $property->getAttributes(Column::class)) {
            throw ORMMappingException::propertyNotAttributed($property->getName());
        }

        if($property->isStatic()) {
            throw ORMMappingException::propertyIsStatic($property->getName());
        }

        try {
            $type = $property->getType();

            if(! $type) {
                throw ORMMappingException::missingTypeDeclaration($property->getName());
            }

            $property->setValue(
                objectOrValue: $model,
                value: PropertySerializationService::deserialize($type, $value)
            );
        }
        catch(Error $e) {
            throw ORMMappingException::cannotCastType($value, $e);
        }
    }
}