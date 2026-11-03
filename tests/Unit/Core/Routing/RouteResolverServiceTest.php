<?php

namespace Tests\Unit\Core\Routing\RouteResolverServiceTest;

use Core\Routing\Controllers\Debug\VoidController;
use Core\Routing\Enums\Method;
use Core\Routing\Objects\Route;
use Core\Routing\Services\RouteResolverService;
use Core\Routing\Services\RouteResolverService as Service;

covers(RouteResolverService::class);

function route(string $definition, Method $method, string $name): Route
{
    return new Route($definition, $method, VoidController::class)
        ->name($name);
}

function service(array $routes, string $uri): Service
{
    return new Service($routes, $uri);
}

describe('Handles simple routes', function ()
{
    test('web root', function () {
        $routes = [
            route('/', Method::GET, 'root'),
            route('/posts', Method::GET, 'posts'),
        ];

        $route = service($routes, '/')->findRouteOrNull();
        $similar = service([
            route('/{slug}', Method::GET, 'similar')
        ], '/')->findRouteOrNull();

        expect($route->name)->toBe('root')
            ->and($similar?->name)->toBeNull();
    });

    test('constant path', function () {
        $routes = [
            route('/', Method::POST, 'root'),
            route('/login', Method::POST, 'login'),
        ];

        $route = service($routes, '/login')->findRouteOrNull();
        $similar = service([route('/login/{token}', Method::POST, 'similar')], '/login')->findRouteOrNull();

        expect($route->name)->toBe('login')
            ->and($similar?->name)->toBeNull();
    });

    test('no routes registered', function () {
        $routes = [];
        $route = service($routes, '/about')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });

    test('path with required segment', function () {
        $routes = [
            route('/posts', Method::GET, 'posts.index'),
            route('/posts/{id%i}', Method::GET, 'posts.show'),
        ];

        $route1 = service($routes, '/posts/1')->findRouteOrNull();
        $route2 = service($routes, '/posts')->findRouteOrNull();
        $similar = service([route('/posts/{id%i}/comments', Method::GET, 'similar')], '/posts/1')->findRouteOrNull();

        expect($route1->name)->toBe('posts.show')
            ->and($similar?->name)->toBeNull();
    });
});

describe('Handles overlapping routes', function ()
{
    it('finds the route registered earlier first', function () {
        $routes = [
            route('/users/{id}', Method::GET, 'user.show'),
            route('/users/joe', Method::GET, 'user.joe'),
        ];

        $route = service($routes, '/users/joe')->findRouteOrNull();
        expect($route->name)->toBe('user.show');
    });

    it('finds the most specific route', function () {
        $routes = [
            route('/users/{slug}', Method::GET, 'user.show'),
            route('/users/{slug}/detailed', Method::GET, 'user.show.detailed'),
            route('/users/{slug}/detailed/json', Method::GET, 'user.show.detailed.json'),
        ];

        $route = service($routes, '/users/jon123/detailed/json')->findRouteOrNull();
        $similar = service(
            [route('/users/{slug}/detailed/json/{format}', Method::GET, 'similar')],
            '/users/jon123/detailed/json'
        )->findRouteOrNull();

        expect($route->name)->toBe('user.show.detailed.json')
            ->and($similar?->name)->toBeNull();
    });

    it('skips a route whose segment type does not match', function () {
        $routes = [
            route('/stops/{id%i}', Method::GET, 'stops.show.id'),
            route('/stops/{name}', Method::GET, 'stops.show.name'),
        ];

        $route1 = service($routes, '/stops/florenc')->findRouteOrNull();
        $route2 = service($routes, '/stops/42')->findRouteOrNull();

        expect($route1?->name)->toBe('stops.show.name')
            ->and($route2?->name)->toBe('stops.show.id');
    });

    it('handles root + top level nullable', function () {
        $routes = [
            route('/', Method::GET, 'root'),
            route('/about', Method::GET, 'about'),
            route('/{?optional}', Method::GET, 'optional'),
        ];

        $root = service($routes, '/')->findRouteOrNull();
        $about = service($routes, '/about')->findRouteOrNull();
        $optional = service($routes, '/opus')->findRouteOrNull();
        $random = service($routes, '/opus/random')->findRouteOrNull();


        expect($root?->name)->toBe('root')
            ->and($about?->name)->toBe('about')
            ->and($optional?->name)->toBe('optional')
            ->and($random?->name)->toBeNull();
    });

    it('handles root + top-level required', function () {
        $routes = [
            route('/', Method::GET, 'root'),
            route('/about', Method::GET, 'about'),
            route('/{model}', Method::GET, 'model'),
        ];

        $root = service($routes, '/')->findRouteOrNull();
        $about = service($routes, '/about')->findRouteOrNull();
        $model = service($routes, '/opus')->findRouteOrNull();
        $random = service($routes, '/opus/random')->findRouteOrNull();

        expect($root?->name)->toBe('root')
            ->and($about?->name)->toBe('about')
            ->and($model?->name)->toBe('model')
            ->and($random?->name)->toBeNull();
    });
});

describe('Handles complex routing scenarios', function ()
{
    it('finds the same route for one with or without the nullable segment', function () {
        $routes = [
            route('users/{?slug}', Method::GET, 'users'),
        ];

        $route1 = service($routes, '/users/joe')->findRouteOrNull();
        $route2 = service($routes, '/users')->findRouteOrNull();
        $similar = service([route('users/{slug}/posts', Method::GET, 'similar')], '/users/joe')->findRouteOrNull();

        expect($route1->name)->toBe('users')
            ->and($route2->name)->toBe('users')
            ->and($similar?->name)->toBeNull();
    });

    it('finds routes with many nullable segments', function (string $path) {
        $routes = [
            route('/route/{?null1}/{?null2}/{?null3}', Method::GET, 'many.nullable')
        ];

        $route = service($routes, $path)->findRouteOrNull();
        $similar = service([route('/route/{r1}/{r2}/{r3}/{r4}', Method::GET, 'similar')], $path)->findRouteOrNull();

        expect($route)->toBeInstanceOf(Route::class)
            ->and($route->name)->toBe('many.nullable')
            ->and($similar?->name)->toBeNull();

    })->with([
        ['path' => '/route'],
        ['path' => '/route/f?foo=bar'],
        ['path' => '/route/f/s'],
        ['path' => '/route/f/s/t?foo=bar'], // tests off-by-one
    ]);

    it('handles URI parameters', function (string $path) {
        $routes = [
            route('/submit', Method::POST, 'submit'),
        ];

        $route = service($routes, $path)->findRouteOrNull();
        expect($route?->name)->toBe('submit');
    })->with([
        ['path' => '/submit'],
        ['path' => '/submit/?foo=bar'],
        ['path' => '/submit?foo=bar'],
    ]);

    it('doesn\'t find routes with more total segments than nullable', function () {
        $routes = [
            route('/route/{?null1}/{?null2}', Method::GET, 'many.nullable')
        ];

        $route = service($routes, '/route/f/s/invalid')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });

    it('does not resolve wildcard parameters containing slashes', function () {
        /*
         * TLDR: wildcards cannot contain slashes (`/`).
         * =====================================================================================================
         * The argument is that each slash should mean a segment, and a `path//to` becomes `['path','','to']`.
         * This breaks wildcards, because a wildcard like `/slashJoe123/` breaks on the `explode()`.
         * Since no normal wildcard should support this, this test is here to document this behavior.
         *
         * Note that this does work for doubled slashes sandwiched between two CONSTANT routes
         * (see test below). This wasn't intended but also doesn't break anything, so I kept it.
         */

        $routes = [
            route('/about/{slug}', Method::GET, 'about.show'),
        ];

        $route = service($routes, '/about//slashJoe124/')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });

    it('handles multiple slashes in-between two CONSTANT segments in the route definition', function () {
        // See test above for comment

        $routes = [
            route('/weird//route', Method::GET, 'weird'),
        ];

        $doubleSlash = service($routes, '/weird//route')->findRouteOrNull();
        $singleSlash = service($routes, '/weird/route')->findRouteOrNull();

        expect($doubleSlash?->name)->toBe('weird')
            ->and($singleSlash?->name)->toBeNull();
    });
});

describe('Skips non-existent and invalid routes', function ()
{
    it('doesn\'t find routes that don\'t exist', function () {
        $routes = [
            route('/', Method::GET, 'root'),
            route('/posts/{id%i}', Method::GET, 'posts.show'),
        ];

        $route = service($routes, '/random/url')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });

    it('doesn\'t resolve routes with const segment between nullable segments', function() {
        $routes = [
            route('/route/{?null1}/const/{?null2}', Method::GET, 'invalid')
        ];

        $route = service($routes, '/route/f/const/s')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });

    it('doesn\'t resolve routes with required segment between nullable segments', function() {
        $routes = [
            route('/route/{?null1}/{required}/{?null2}', Method::GET, 'invalid')
        ];

        $route = service($routes, '/route/f/s/t')->findRouteOrNull();
        expect($route?->name)->toBeNull();
    });
});