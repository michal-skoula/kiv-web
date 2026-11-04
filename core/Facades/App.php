<?php

namespace Core\Facades;

use Core\Application\Application;
use Core\Application\DataStores\PathsManager;
use Core\Logging\Logger;
use Core\Routing\Objects\Router;
use Pdo\Sqlite;

/**
 * Unified facade for providing access to app services.
 */
final class App
{
    private static ?Application $app = null;

    public static function initialize(Application $app): void
    {
        if(self::$app) {
            self::$app->logger->warning("Attempting to re-initialize App facade");
            return;
        }

        self::$app = $app;
    }

    public static function instance(): Application
    {
        return self::$app;
    }

    public static function inProduction(): bool
    {
        return self::instance()->environment === 'production';
    }

    public static function paths(): PathsManager
    {
        return self::instance()->paths;
    }

    public static function db(): Sqlite
    {
        return self::instance()->db;
    }

    public static function log(): Logger
    {
        return self::instance()->logger;
    }

    public static function router(): Router
    {
        return self::instance()->router;
    }
}