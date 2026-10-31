<?php

namespace Core\Facades;

use Exception;
use PDO;
use Core\Application;
use Core\Exceptions\ApplicationException;
use Core\Logging\Logger;

/**
 * Unified facade for providing access to app services.
 */
final class App
{
    private static ?Application $app = null;

    /**
     * @throws ApplicationException Attempting to re-initialize the app.
     */
    public static function initialize(Application $app): void
    {
        if(self::$app) {
            throw new ApplicationException("Application already initialized");
        }

        self::$app = $app;
    }

    public static function instance(): Application
    {
        return self::$app;
    }

    public static function env(): string
    {
        return self::$app->env;
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