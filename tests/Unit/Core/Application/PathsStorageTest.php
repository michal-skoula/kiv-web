<?php

namespace Tests\Unit\Core\Application\PathsStorageTest;

use Core\App\DataStores\PathsManager;

covers(PathsManager::class);

beforeEach(function () {
    $this->root = sys_get_temp_dir() . '/paths-storage-test-' . uniqid();
    mkdir($this->root . '/dir/dir2', recursive: true);
    touch($this->root . '/file.php');

    $this->paths = new PathsManager($this->root);
});

afterEach(function () {
    unlink($this->root . '/file.php');
    rmdir($this->root . '/dir/dir2');
    rmdir($this->root . '/dir');
    rmdir($this->root);
});

describe('pathTo', function ()
{
    test('resolves to an absolute path without duplicate or trailing slashes', function (string $relative, string $expected) {
        expect($this->paths->pathTo($relative))->toBe($this->root . $expected);
    })->with([
        'file'                         => ['file.php', '/file.php'],
        'file with leading slash'      => ['/file.php', '/file.php'],
        'dir'                          => ['dir/dir2', '/dir/dir2'],
        'dir with leading slash'       => ['/dir/dir2', '/dir/dir2'],
        'dir with trailing slash'      => ['dir/dir2/', '/dir/dir2'],
        'dir with both slashes'        => ['/dir/dir2/', '/dir/dir2'],
    ]);

    test('returns null when the path is null', function () {
        expect($this->paths->pathTo(null))->toBeNull();
    });

    test('returns null when the path does not exist', function () {
        expect($this->paths->pathTo('missing.php'))->toBeNull();
    });
});
