<?php

namespace Core\Application\DataStores;

use Core\Helpers\Traits\NormalizesPaths;

/**
 * Data storage object containing all app paths and helpful accessors.
 *
 * **IMPORTANT** All paths are returned as absolute paths with a leading slash and without a trailing slash.
 */
final class PathsStorage
{
    use NormalizesPaths;

    /**
     * @var string Normalized, absolute path to app root.
     */
    private readonly string $appRoot;

    /**
     * @var string Routes file.
     */
    private string $routes;

    /**
     * @var string Migrations directory.
     */
    private string $migrations;

    public function __construct(string $appRoot)
    {
        $this->appRoot = $this->normalizePath($appRoot);
    }

    /**
     * Set the entrypoint for the application's routes file relative to app root.
     *
     * @param string $path Relative path to routes php file.
     *
     * @return self
     */
    public function setRoutesPath(string $path): self
    {
        $this->routes = $path;
        return $this;
    }

    /**
     * Set the directory for the application's migrations directory relative to app root.
     *
     * @param string $path Relative path to migrations directory.
     *
     * @return self
     */
    public function setMigrationsPath(string $path): self
    {
        $this->migrations = $path;
        return $this;
    }

    /**
     * Path to application root (the one containing composer.json)
     *
     * @return string
     */
    public function root(): string
    {
        return $this->appRoot;
    }

    /**
     * Produces an absolute path from a relative one, or returns null if non-existent or undefined.
     *
     * @param string|null $relativePath Relative path, which can be unset.
     * @return string|null
     */
    public function pathTo(?string $relativePath): ?string
    {
        if(! isset($relativePath)) {
            return null;
        }

        // normalizing on each setter seemed bug-prone, so this is unified here
        $relativeNormalized = self::normalizePath($relativePath);
        $path = self::normalizePath($this->root() . $relativeNormalized);

        if(! file_exists($path)) {
            return null;
        }

        return $path;
    }

    public function routesFile(): ?string
    {
        return $this->pathTo($this->routes);
    }

    public function migrationsDir(): ?string
    {
        return $this->pathTo($this->migrations);
    }
}