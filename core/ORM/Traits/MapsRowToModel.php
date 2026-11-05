<?php

namespace Core\ORM\Traits;

use Core\Exceptions\ORMMappingException;
use Core\ORM\Attributes\Column;
use Core\ORM\Contracts\Serializer;
use Core\ORM\Model;
use Core\ORM\Services\PropertySerializationService;
use Error;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait MapsRowToModel
{
    /**
     * Caches all mappings resolved via reflection so each is only discovered once.
     *
     * @var array<class-string<Model>, array<string, class-string<Serializer>|null>> The
     * array key is the model's FQCN, and the value in associative array of #[Column]
     * properties and their serializer handler, or null if it's handled by PHP.
     *
     * todo: implement
     */
    private static array $mappingCache;

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