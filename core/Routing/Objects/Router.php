<?php

namespace Core\Routing\Objects;

use Closure;
use Core\Exceptions\NotImplementedException;
use Core\Routing\Contracts\Controller;
use Core\Routing\Controllers\NotFoundController;
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
    public readonly Request $request;

    /**
     * Controller responsible for all requests which don't resolve to a defined Route.
     *
     * @var class-string<Controller>|Closure
     */
    private string|Closure $fallbackController;

    /**
     * @param Request $request
     * @param class-string<Controller> $fallbackController
     */
    public function __construct(Request $request, string $fallbackController = NotFoundController::class)
    {
        $this->request = $request;
        $this->fallbackController = $fallbackController;
    }

    /**
     * Registers a Route in the router.
     *
     * @param Route $route
     * @return void
     */
    public function registerRoute(Route $route): void
    {
        $type = $route->method->value;
        $this->routes[$type][] = $route;
    }

    /**
     * Sets the controller responsible for handling all fall-through traffic.
     *
     * @param class-string<Controller>|Closure $controller
     * @return void
     */
    public function setFallbackController(string|Closure $controller): void
    {
        $this->fallbackController = $controller;
    }

    public function handleRequest(): Response
    {
        if($this->request->method === Method::UNSUPPORTED) {
            return new Response(405, "Method Not Allowed");
        }

        $route = $this->resolveRoute();

        /** @var class-string<Controller>|Closure $handler */
        $handler = $route?->handler ?? $this->fallbackController;

        // todo: this code is kind of a mess and the test covering it is really screaming
        //       "turn this into a service!!" - refactor when doing Views.
        $result = $handler instanceof Closure
            ? $handler($this->request, $route)
            : new $handler($this->request, $route)->handle();

        if($result instanceof Response) {
            return $result;
        }
        else if($result instanceof View) {
            throw new NotImplementedException("View handling isn't handled yet in the router.");
        }
        else {
            return Response::serverError('Something went wrong on our end.');
        }
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