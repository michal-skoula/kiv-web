<?php

namespace Core\Routing\Objects;

use Closure;
use Core\Exceptions\RoutingException;
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
     * The Route's expected http method.
     *
     * @var Method
     */
    public Method $method;

    /**
     * The Route's handler.
     *
     * @var class-string<Controller>|Closure(Request, Route): (View|Response)
     */
    public Closure|string $handler;

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
     * @param Method $method
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $handler Controller class, or an inline handler (mainly for tests)
     * @throws RoutingException
     */
    public function __construct(
        string         $route,
        Method         $method,
        string|Closure $handler
    ) {
        // All routes start from app root, making trailing slashes on either end unnecessary.
        $this->definition = trim($route, '/');
        if($method === Method::UNSUPPORTED) {
            throw new RoutingException("Routes cannot be registered with an UNSUPPORTED method.");
        }
        $this->method = $method;
        $this->handler = $handler;
    }

    /**
     * Registers a new route.
     *
     * @param string $route
     * @param Method $type
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function make(string $route, Method $type, string|Closure $handler): static
    {
        $route = new static($route, $type, $handler);

        App::instance()->router->registerRoute($route);

        return $route;
    }

    /**
     * Registers a new GET route.
     *
     * @param string $route Route definition
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $controller Handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function get(string $route, string|Closure $controller): static
    {
        return static::make($route, Method::GET, $controller);
    }

    /**
     * Registers a new POST route.
     *
     * @param string $route Route definition
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $controller Handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function post(string $route, string|Closure $controller): static
    {
        return static::make($route, Method::POST, $controller);
    }

    /**
     * Registers a new PATCH route.
     *
     * @param string $route Route definition
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $controller Handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function patch(string $route, string|Closure $controller): static
    {
        return static::make($route, Method::PATCH, $controller);
    }

    /**
     * Registers a new PUT route.
     *
     * @param string $route Route definition
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $controller Handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function put(string $route, string|Closure $controller): static
    {
        return static::make($route, Method::PUT, $controller);
    }

    /**
     * Registers a new DELETE route.
     *
     * @param string $route Route definition
     * @param class-string<Controller>|Closure(Request, Route): (View|Response) $controller Handler
     *
     * @return static
     * @throws RoutingException
     */
    public static function delete(string $route, string|Closure $controller): static
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