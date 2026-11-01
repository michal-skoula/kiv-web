<?php

namespace Core\Routing\Objects;

use Core\Facades\App;
use Core\Routing\Contracts\Controller;
use Core\Routing\Controllers\DebugController;

final class Router
{
    /**
     * Registered routes via `registerRoute`, keyed by route type for faster lookup.
     *
     * @var array{string, array<Route>}
     */
    public array $routes = [];

    /**
     * Controller responsible for all requests which don't resolve to a defined Route.
     *
     * @var class-string<Controller>
     */
    private string $fallbackController = DebugController::class;

    public function __construct(
        public $routesPath
    ) {}

    public function registerRoute(Route $route): void
    {
        $type = $route->type->value;
        $this->routes[$type] = $route;
    }

    /**
     * @param class-string<Controller> $controller
     * @return void
     */
    public function registerFallbackController(string $controller): void
    {
        $this->fallbackController = $controller;
    }

    /**
     * @return class-string<Controller>
     */
     public function getFallbackController(): string
     {
         return $this->fallbackController;
     }


    public function handleRequest(): void
    {
        $route = $this->resolveRoute();

        // Couldn't find
        if($route === null) {
            /** @var Controller $fallbackController */
            $fallbackController = new $this->fallbackController(new Request());
            $fallbackController->handle();
        }

    }

    private function resolveRoute(): Route|null
    {
        return null;
    }
}