<?php

use Core\Routing\Controllers\NotFoundController;
use Core\Routing\Controllers\VoidController;
use Core\Routing\Enums\Method;
use Core\Routing\Objects\Request;
use Core\Routing\Objects\Response;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\Router;
use Tests\Unit\Core\Routing\Fixtures\FakeRequest;

covers(Router::class);

function route(Method $method = Method::GET, ?string $route = null): Route
{
    $route ??= '/test/route' . rand(1, 100_000_000);

    return new Route($route , $method, fn() => Response::ok());
}

/**
 * Hard-codes NotFoundController as the fallback for all tests.
 */
function router(Request $request): Router
{
    return new Router($request, fallbackController: NotFoundController::class);
}

describe('Routing', function ()
{
    it('registers routes correctly', function () {
        $router = router(new FakeRequest);
        $router->registerRoute(route(Method::PATCH));
        $router->registerRoute(route(Method::POST));
        $router->registerRoute(route(Method::PATCH));

        expect($router->routes['patch'])->toHaveLength(2)
            ->and($router->routes['post'])->toHaveLength(1);
    });

    it('405s requests with an unknown HTTP method', function () {
        $router = router(new FakeRequest(Method::UNSUPPORTED, uri: '/about'));
        $router->registerRoute(route(Method::GET, '/about'));

        $response = $router->handleRequest();
        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($response->status)->toBe(405);
    });

    it('uses the fallback controller if no route was resolved', function () {
        $router = new Router(new FakeRequest(uri: '/about'));
        $router->registerRoute(route(route: 'bogus1'));
        $router->registerRoute(route(route: 'bogus2'));

        $controller = fn() => Response::notFound();
        $router->setFallbackController($controller);


        $response = $router->handleRequest();
        expect($response)->toBeInstanceOf(Response::class)
            ->and($response->status)->toBe(404);
    });
});

describe('Handlers', function ()
{
    it('supports Controller-handled route returns a Response', function () {
        $router = router(new FakeRequest(uri: '/about'));
        $router->registerRoute(new Route('/about', Method::GET, VoidController::class));

        $response = $router->handleRequest();
        expect($response)->toBeInstanceOf(Response::class)
            ->and($response->status)->toBe(200);
    });

    it('supports a closure-handled route and returns a Response', function () {
        $router = router(new FakeRequest(uri: '/about'));
        $router->registerRoute(new Route('/about', Method::GET, fn(): Response => Response::ok()));

        $response = $router->handleRequest();
        expect($response)->toBeInstanceOf(Response::class)
            ->and($response->status)->toBe(200);
    });


});

describe('Views', function ()
{
    it('handles views', function () {
        todo('Views haven\'t been implemented yet');
    });
});