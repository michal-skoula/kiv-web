<?php

namespace Core\Routing\Services;

use Core\Exceptions\RoutingException;
use Core\Routing\BoundBy;
use Core\Routing\Enums\SegmentDataType;
use Core\Routing\Objects\RouteParameter;
use ReflectionEnumBackedCase;
use Throwable;

class RouteObjectBindingService
{
    /**
     * Resolves and binds Classes from route parameters,
     * or throws when it is unable to do so.
     *
     * @param array<string, RouteParameter> $routeParams
     *
     * @return array<string, mixed> Route parameters with bindings.
     *
     * @throws RoutingException 404 Not Found, normal and expected
     * @throws Throwable 500 server error (ex. couldn't find class, likely misconfigured route)
     */
    public static function bind(array $routeParams): array
    {
        $params = [];

        foreach($routeParams as $key => $param)
        {
            $reflector = new ReflectionEnumBackedCase(SegmentDataType::class, $param->type->name);
            $isBound = $reflector->getAttributes(BoundBy::class);

            if(! $isBound) {
                $params[$key] = $param->value;
                continue;
            }

            /** @var BoundBy $bindingResolver */
            $bindingResolver = $isBound[0]->newInstance();
            $data = $bindingResolver->handler::resolve(name: $key, value: $param->value);
            $params[$key] = $data;
        }

        return $params;
    }
}