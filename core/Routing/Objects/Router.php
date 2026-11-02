<?php

namespace Core\Routing\Objects;

use Closure;
use Core\Exceptions\NotImplementedException;
use Core\Routing\Contracts\Controller;
use Core\Routing\Controllers\DebugController;
use Core\Routing\Enums\Method;
use Core\Routing\Services\RouteResolverService;

final class Router
{
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

    public function __construct(Request $request)
    {
        $this->request = $request;
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

    public function handleRequest(): never
    {
        if($this->request->method === Method::UNSUPPORTED) {
            new Response(405, "Method Not Allowed")
                ->send();
        }

        $route = $this->resolveRoute();

        /** @var class-string<Controller>|Closure $handler */
        $handler = $route?->controller ?? $this->fallbackController;

        // todo: make this return, and handle the `die;` in a different method so this is testable.
        $result = $handler instanceof Closure
            ? $handler($this->request, $route)
            : new $handler($this->request, $route)->handle();

        // todo: this as separate method
        if($result instanceof Response) {
            $result->send();
        }
        else if($result instanceof View) {
            throw new NotImplementedException("View handling isn't handled yet in the router.");
        }

        // todo: finish this handling properly
        die;
    }

    private function resolveRoute(): Route|null
    {
        $method = $this->request->method->value;
        $routes = $this->routes[$method] ?? [];

        if(! $routes) {
            return null;
        }

        return new RouteResolverService($routes, $this->request->uri)
            ->findRouteOrNull();
    }
}