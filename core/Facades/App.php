<?php

namespace Core\Facades;

use Core\Application\Application;
use Core\Application\DataStores\PathsStorage;
use Core\Exceptions\ApplicationException;
use Core\Logging\Logger;
use PDO;

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

    public static function isProduction(): bool
    {
        return self::$app->environment === 'production';
    }

    public static function paths(): PathsStorage
    {
        return self::$app->paths;
    }

    public static function db(): PDO
    {
        return self::$app->db;
    }

    public static function log(): Logger
    {
        return self::$app->logger;
    }
}