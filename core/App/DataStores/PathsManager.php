<?php

namespace Core\App\DataStores;

use Core\Helpers\Traits\NormalizesPaths;

/**
 * Object for configuring and reading application paths.
 *
 * ```
 * // Path relative to app root
 * ->set<Name>Path(string $path): void
 * ```
 *
 * ```
 * // Files
 * ->get<Name>File(string $filename): string
 * // Directories
 * ->get<Name>Dir(): string
 * ```
 *
 *
 */
final class PathsManager
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
     * Path to application root (the one containing composer.json)
     *
     * @return string
     */
    public function root(): string
    {
        return $this->appRoot;
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
     * Produces an absolute path **WITHOUT A TRAILING SLASH** from a
     * relative one, or returns null if non-existent or undefined.
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
        return self::normalizePath($this->root() . $relativeNormalized);
    }

    public function getMigrationsDir(): string
    {
        return $this->pathTo($this->migrations);
    }

    public function getMigrationFile(string $filename): string
    {
        return $this->pathTo($this->migrations . '/' . $filename);
    }

    public function getRoutesFile(): string
    {
        return $this->pathTo($this->routes);
    }
}