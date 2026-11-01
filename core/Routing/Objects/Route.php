<?php

namespace Core\Routing\Objects;

use Core\Facades\App;
use Core\Routing\Contracts\Controller;
use Core\Routing\Contracts\Middleware;
use Core\Routing\Enums\Method;

class Route
{
    /**
     * The route's name.
     *
     * Example: `users.list`
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * The route's definition (the path)
     *
     * Example: `/users/{userId%i}`
     *
     * @var string
     */
    public string $definition;

    /**
     * Ordered list of middleware guarding this route.
     *
     * @var list<class-string<Middleware>>
     */
    protected array $middleware = [];

    /**
     * Creates a new route.
     *
     * @param string $route
     * @param Method $type
     * @param class-string<Controller> $controller
     */
    public function __construct(
        string $route,
        public Method $type,
        public string $controller
    ) {
        // All routes start from app root, making trailing slashes on either end unnecessary.
        $this->definition = trim($route, '/');
    }

    /**
     * Registers a new route.
     *
     * @param string $route
     * @param Method $type
     * @param string $controller
     *
     * @return static
     */
    public static function make(string $route, Method $type, string $controller): static
    {
        $route = new static($route, $type, $controller);

        App::instance()->router->registerRoute($route);

        return $route;
    }

    /**
     * Registers a new GET route.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function get(string $route, string $controller): static
    {
        return static::make($route, Method::GET, $controller);
    }

    /**
     * Registers a new POST route.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function post(string $route, string $controller): static
    {
        return static::make($route, Method::POST, $controller);
    }

    /**
     * Registers a new PATCH route.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function patch(string $route, string $controller): static
    {
        return static::make($route, Method::PATCH, $controller);
    }

    /**
     * Registers a new PUT route.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function put(string $route, string $controller): static
    {
        return static::make($route, Method::PUT, $controller);
    }

    /**
     * Registers a new DELETE route.
     *
     * @param string $route Route definition
     * @param string $controller Handler
     *
     * @return static
     */
    public static function delete(string $route, string $controller): static
    {
        return static::make($route, Method::DELETE, $controller);
    }

    /**
     * Replaces the default route fallback controller with a custom one.
     *
     * @param string $controller
     * @return void
     */
    public static function fallback(string $controller): void
    {
        App::instance()->router->setFallbackController($controller);
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array<class-string<Middleware>> $middleware
     * @return self
     */
    public function middleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }
}