<?php

namespace Core\Routing\Services\BindingResolvers;

use Core\Exceptions\RoutingException;
use Core\ORM\Model;
use Core\ORM\Traits\ResolvesModelFromName;
use Core\Routing\Contracts\BindingResolver;

final class ModelResolver implements BindingResolver
{
    use ResolvesModelFromName;

    /**
     * @inheritDoc
     *
     * @param int $value
     *
     * @return Model
     */
    public static function resolve(string $name, mixed $value): mixed
    {
        $modelClass = self::getModelClass($name);

        return $modelClass::find($value)
            ?? throw new RoutingException("No '$name' found with id $value.");
    }
}
