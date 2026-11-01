<?php

namespace Core;

use Core\Exceptions\ApplicationException;
use Core\Facades\App as AppFacade;
use Core\Logging\Logger;
use Core\Logging\LogLevel;
use Core\Routing\Objects\Router;
use Dotenv\Dotenv;
use PDO;
use PDOException;
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

    /** @var Router Application router. */
    public Router $router;

    /**
     * Builds the application and initializes facades.
     *
     * @param string $rootDir Full path to the root directory of the application.
     * @param ?string $routes Relative path to file registering routes from app root.
     * @param array<string, string> $customEnv Add or override environment variables outside of .env.
     *
     * @throws ApplicationException
     */
    public static function bootstrap(
        string $rootDir,
        ?string $routes = null,
        array $customEnv = []
    ): void
    {
        $application = new self($rootDir, $routes, $customEnv);

        AppFacade::initialize($application);
    }

    /**
     * Builds the application instance.
     *
     * @throws ApplicationException
     */
    public function __construct(
        string $rootDir,
        public ?string $routes = null,
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
     * @param array $customEnv Env KEY=value pairs passed via Application bootstrapper.
     *
     * @throws ApplicationException
     */
    private function bootstrapEnv(array $customEnv): void
    {
        try {
            $dotenv = Dotenv::createMutable(__DIR__ . '/../');
            $dotenv->load();
        } catch (\Throwable $e) {
            throw ApplicationException::from($e, 'Couldn\'t find sqlite file.');
        }


        // This will override existing .env values.
        foreach ($customEnv as $key => $value) {
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

    private function pathFromRootDir(string $path): string
    {
        $root = trim($this->rootDir, '/');
        $trimmed = trim($path, '/');

        return "/{$root}/{$trimmed}";
    }

    public function hasRoutingEnabled(): bool
    {
        return isset($this->routes);
    }

    /**
     * **IMPORTANT** Must run after the Application is fully initialized.
     *
     * @throws ApplicationException
     */
    public function handleHttpRequests(): void
    {
        if(! $this->hasRoutingEnabled()) {
            $this->logger->critical("App doesn't have routing enabled but is attempting to handle HTTP requests.");
            echo "Error 500";
            exit(1);
        }

        $this->router = new Router($this->pathFromRootDir($this->routes));

        if(! file_exists($this->router->routesPath)) {
            $this->logger->critical("Couldn't find routes file at {$this->router->routesPath}. App is not functional.");
            echo "Error 500";
            exit(1);
        }

        // Loads in all `Route::` definitions.
        require_once $this->router->routesPath;

        $this->logger->info("Router is listening for HTTP requests.");

        $this->router->handleRequest();
    }
}