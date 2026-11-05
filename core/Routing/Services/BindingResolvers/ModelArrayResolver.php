<?php

namespace Core\Routing\Services\BindingResolvers;

use Core\Exceptions\ModelException;
use Core\Exceptions\RoutingException;
use Core\ORM\Model;
use Core\ORM\Traits\ResolvesModelFromName;
use Core\Routing\Contracts\BindingResolver;

final class ModelArrayResolver implements BindingResolver
{
    use ResolvesModelFromName;

    /**
     * @inheritDoc
     *
     * @param int[] $value
     *
     * @return Model[]
     */
    public static function resolve(string $name, mixed $value): mixed
    {
        $modelClass = self::getModelClass($name);

        try {
            return $modelClass::findAll($value);
        } catch (ModelException $e) {
            throw RoutingException::from($e, "Couldn't find one or more '$name' models.");
        }
    }
}
