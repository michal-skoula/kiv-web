<?php

namespace PidAnalyzer\Core;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use PidAnalyzer\Core\Exceptions\ApplicationException;
use PidAnalyzer\Core\Logging\Logger;
use PidAnalyzer\Core\Logging\LogLevel;
use ValueError;

final readonly class Application
{
    public string $env;

    /** @var string Path to app root, without a trailing slash. */
    public string $rootDir;

    /**
     * @var PDO $db Connected database instance.
     */
    public PDO $db;

    /**
     * @var Logger Configured logger.
     */
    public Logger $logger;

    /**
     * @param string $rootDir Full path to the root directory of the application.
     * @param array<string, string> $customArgs Add or override environment variables outside of .env.
     *
     * @throws ApplicationException
     */
    public function __construct(
        string $rootDir,
        array $customArgs = []
    ) {
        $this->rootDir = rtrim($rootDir, '/');

        $this->bootstrapEnv($customArgs);
        $this->bootstrapLogger();
        $this->bootstrapDb();

    }

    /**
     * @throws ApplicationException
     */
    private function bootstrapDb(): void
    {
        try {
            $conn = new PDO("sqlite:{$this->rootDir}/{$_ENV['DB_PATH']}");

            // This makes PDO throw a PDOException on failure.
            // @see https://www.php.net/manual/en/pdo.error-handling.php
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Makes PDO return a key => value array, ex. `[id => 1]` instead of the weird default.
            // @see https://www.php.net/manual/en/pdo.constants.fetch-modes.php
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $this->db = $conn;
            $this->logger->info("SQLite database connected successfully.");
        }
        catch(PDOException $e) {
            $this->logger->error($e->getMessage());
            throw ApplicationException::from($e);
        }
    }

    /**
     * @param array $customArgs Args passed via Application constructor.
     *
     * @throws ApplicationException
     */
    private function bootstrapEnv(array $customArgs): void
    {
        try {
            $dotenv = Dotenv::createMutable(__DIR__ . '/../../');
            $dotenv->load();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw ApplicationException::from($e, 'Couldn\'t find sqlite file.');
        }


        // This will override existing .env values.
        foreach ($customArgs as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $this->env = $_ENV['APP_ENV'] ?? 'production';
    }

    /**
     * @throws ApplicationException
     */
    private function bootstrapLogger(): void
    {
        try {
            $logLevel = LogLevel::from($_ENV['LOG_LEVEL']);
        } catch(ValueError $e) {
            throw ApplicationException::from($e, 'Invalid LOG_LEVEL environment variable.');
        }

        $this->logger = new Logger($logLevel);
    }
}