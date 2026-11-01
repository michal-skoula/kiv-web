<?php

namespace Core\Routing\Objects;

use Core\Facades\App;
use Core\Routing\Contracts\Controller;
use Core\Routing\Contracts\Middleware;
use Core\Routing\Enums\Method;

class Route
{
    /** @var array<class-string<Middleware>>  */
    protected array $middleware = [];

    /**
     * @param string $route
     * @param Method $type
     * @param class-string<Controller> $controller
     */
    public function __construct(
        public string $route,
        public Method $type,
        public string $controller
    ){
        App::instance()->router->registerRoute($this);
    }

    /**
     * Creates a new GET route builder.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function get(string $route, string $controller): static
    {
        return new static($route, Method::GET, $controller);
    }

    /**
     * Creates a new POST route builder. Register it by calling
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function post(string $route, string $controller): static
    {
        return new static($route, Method::POST, $controller);
    }

    public static function patch(string $route, string $controller): static
    {
        return new static($route, Method::PATCH, $controller);
    }

    public static function put(string $route, string $controller): static
    {
        return new static($route, Method::PUT, $controller);
    }

    public static function delete(string $route, string $controller): static
    {
        return new static($route, Method::DELETE, $controller);
    }

    /**
     * Replaces the default route fallback controller with a custom one.
     *
     * @param string $controller
     * @return void
     */
    public static function fallback(string $controller): void
    {
        App::instance()->router->registerFallbackController($controller);
    }

    /**
     * @param array<class-string<Middleware>> $middleware
     * @return self
     */
    public function middleware(array $middleware): self
    {
        $this->middleware = $middleware;

        return $this;
    }
}