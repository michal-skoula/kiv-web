<?php

namespace Core\Routing\Objects;

use Core\Exceptions\NotImplementedException;
use Core\Routing\Contracts\Controller;
use Core\Routing\Controllers\DebugController;
use Core\Routing\Enums\Method;
use Core\Routing\Services\RouteResolverService;

final class Router
{
    /**
     * @deprecated should be handled by Application, not the router.
     *
     * @var string
     */
    public string $routesPath;

    /**
     * Registered routes via `registerRoute`, keyed by route type.
     *
     * @var array{string, array<Route>}
     */
    public array $routes = [];

    /**
     * The Request object.
     *
     * @var Request
     */
    public Request $request;

    /**
     * Controller responsible for all requests which don't resolve to a defined Route.
     *
     * @var class-string<Controller>
     */
    private string $fallbackController = DebugController::class;

    public function __construct(string $routesPath)
    {
        $this->routesPath = $routesPath;
        $this->request = Request::get();
    }

    /**
     * Registers a Route in the router.
     *
     * @param Route $route
     * @return void
     */
    public function registerRoute(Route $route): void
    {
        $type = $route->type->value;
        $this->routes[$type][] = $route;
    }

    /**
     * Sets the controller responsible for handling all fall-through traffic.
     *
     * @param class-string<Controller> $controller
     * @return void
     */
    public function setFallbackController(string $controller): void
    {
        $this->fallbackController = $controller;
    }

    public function handleRequest(): void
    {
        if($this->request->method === Method::UNSUPPORTED) {
            Response::make()
                ->status(405)
                ->body("Method not supported")
                ->send();
        }

        $route = $this->resolveRoute();

        /** @var class-string<Controller> $controller */
        $controller = ! $route
            ? $this->fallbackController
            : $route->controller;

        $result = new $controller($this->request, $route)->handle();

        if($result instanceof Response) {
            $result->send();
        }
        else if($result instanceof View) {
            throw new NotImplementedException("View handling isn't handled yet in the router.");
        }
    }

    private function resolveRoute(): Route|null
    {
        $method = $this->request->method->value;
        $sameMethodRoutes = $this->routes[$method] ?? [];

        if(! $sameMethodRoutes) {
            return null;
        }

        return new RouteResolverService($sameMethodRoutes, $this->request->uri)
            ->findRouteOrNull();
    }
}