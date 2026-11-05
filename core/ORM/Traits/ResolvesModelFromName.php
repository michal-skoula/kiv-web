<?php

namespace Core\ORM\Traits;

use Core\ORM\Model;

trait ResolvesModelFromName
{
    /**
     * fixme: Hard-coded namespace on a trait isn't great, move it onto Application or a Manager class
     */
    public const string MODELS_NAMESPACE = 'App\\Models\\';

    /**
     * @param string $name
     * @return class-string<Model>
     */
    protected static function getModelClass(string $name): string
    {
        return self::MODELS_NAMESPACE . ucfirst($name);
    }

}