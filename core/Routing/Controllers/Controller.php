<?php

namespace Core\Routing\Controllers;

use Core\Routing\Objects\Request;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteParameter;
use Core\Routing\Objects\View;

abstract class Controller
{
    /**
     * @param Request $request The HTTP Request.
     * @param Route|null $route The resolved route, or null if none matched.
     * @param array<string, RouteParameter> $parameters Array of parameters resolved from wildcards.
     */
    public function __construct(
        protected Request $request,
        protected ?Route $route = null,
        protected array $parameters = []
    ) {
        //  🎮🎮🎮 It's controllin' time 🎮🎮🎮 \\
    }

    abstract public function handle(): View|Response;
}