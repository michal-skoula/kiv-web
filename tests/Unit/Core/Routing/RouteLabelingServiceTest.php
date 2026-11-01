<?php

namespace Tests\Unit\Core\Routing\RouteLabelingServiceTest;

use Core\Routing\Controllers\VoidController;
use Core\Routing\Enums\Method;
use Core\Routing\Enums\SegmentDataType as Type;
use Core\Routing\Enums\SegmentShape as Shape;
use Core\Routing\Objects\Route;
use Core\Routing\Objects\RouteSegment;
use Core\Routing\Services\RouteLabelingService;

covers(RouteLabelingService::class);

function route(string $definition): Route
{
    return new Route($definition, Method::GET, VoidController::class);
}

describe('Labels all route segment shapes', function()
{
    test('shape', function(string $route, string $name) {
        $labelled = new RouteLabelingService(route($route))->label();

        expect($labelled)
            ->toHaveLength(1)
            ->and($labelled[0])->toBeInstanceOf(RouteSegment::class)
            ->and($labelled[0]->shape)->toBe(Shape::from($name));

    })->with([
        ['route' => '/constant', 'name' => 'constant'],
        ['route' => '/{required}', 'name' => 'required'],
        ['route' => '/{?nullable}', 'name' => 'nullable'],
    ]);

    test('unclosed (typo) wildcard segment is treated as constant', function() {
        $labelled = new RouteLabelingService(route('/{typo'))->label();
        expect($labelled)
            ->toHaveLength(1)
            ->and($labelled[0]->shape)->toBe(Shape::CONSTANT);
    });
});

describe('Labels all route segment data types', function() {
    test('data type', function(string $route, string $type) {
        $labelled = new RouteLabelingService(route($route))->label();

        expect($labelled)->toHaveLength(1)
            ->and($labelled[0])->toBeInstanceOf(RouteSegment::class)
            ->and($labelled[0]->type)->toBe(Type::from($type));

    })->with([
        ['route' => '/constant', 'type' => 'none'],
        ['route' => '/{id}', 'type' => 'string'],
        ['route' => '/{id%s}', 'type' => 'string'],
        ['route' => '/{id%i}', 'type' => 'integer'],
    ]);
});

describe('Extracts route segment names', function() {
    test('name', function(string $route, string $name) {
        $labelled = new RouteLabelingService(route($route))->label();

        expect($labelled)->toHaveLength(1)
            ->and($labelled[0])->toBeInstanceOf(RouteSegment::class)
            ->and($labelled[0]->name)->toBe($name);

    })->with([
        ['route' => '/constant', 'name' => 'constant'],
        ['route' => '/{id}', 'name' => 'id'],
        ['route' => '/{?id}', 'name' => 'id'],
        ['route' => '/{id%i}', 'name' => 'id'],
        ['route' => '/{?id%i}', 'name' => 'id'],
    ]);
});

describe('Slashes at the start and end', function()
{
    it('correctly labels a route without a starting slash', function() {
        $labelled = new RouteLabelingService(route('posts/{id}'))->label();

        expect($labelled)
            ->toHaveLength(2)
            ->and($labelled[0]->name)->toBe('posts')
            ->and($labelled[1]->name)->toBe('id');
    });

    it('correctly labels a route with a trailing slash', function() {
        $labelled = new RouteLabelingService(route('/posts/{id}/'))->label();

        expect($labelled)
            ->toHaveLength(2)
            ->and($labelled[0]->name)->toBe('posts')
            ->and($labelled[1]->name)->toBe('id');
    });
});

test('Labels routes with multiple segments', function() {
    $labelled = new RouteLabelingService(route('/users/{id%i}/posts/{?slug%s}/'))->label();

    expect($labelled)
        ->toHaveLength(4)
        ->each->toBeInstanceOf(RouteSegment::class)
        // 1st
        ->and($labelled[0]->shape)->toBe(Shape::CONSTANT)
        ->and($labelled[0]->type)->toBe(Type::NONE)
        // 2nd
        ->and($labelled[1]->shape)->toBe(Shape::REQUIRED)
        ->and($labelled[1]->type)->toBe(Type::INTEGER)
        // 3rd
        ->and($labelled[2]->shape)->toBe(Shape::CONSTANT)
        ->and($labelled[2]->type)->toBe(Type::NONE)
        // 4th
        ->and($labelled[3]->shape)->toBe(Shape::NULLABLE)
        ->and($labelled[3]->type)->toBe(Type::STRING);
});
