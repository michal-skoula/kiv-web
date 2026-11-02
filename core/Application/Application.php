<?php

namespace Core\Application;

use Core\Application\DataStores\PathsStorage;
use Core\Exceptions\ApplicationException;
use Core\Facades\App as AppFacade;
use Core\Logging\Logger;
use Core\Logging\LogLevel;
use Core\Routing\Objects\Request;
use Core\Routing\Objects\Router;
use Dotenv\Dotenv;
use PDO;
use PDOException;
use Throwable;
use ValueError;

final readonly class Application
{
    /**
     * What environment the application is in.
     *
     * Example: `production`, `local`.
     *
     * @var string
     */
    public string $environment;

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
     * Storage object for application paths.
     *
     * @var PathsStorage
     */
    public PathsStorage $paths;

    /**
     * Builds the application and initializes facades.
     *
     * @param PathsStorage $paths
     * @param array<string, string> $customEnv Add or override environment variables outside of .env.
     *
     * @throws ApplicationException
     */
    public static function bootstrap(
        PathsStorage $paths,
        array        $customEnv = []
    ): void
    {
        $application = new self($paths,$customEnv);

        AppFacade::initialize($application);
    }

    /**
     * Builds the application instance.
     *
     * @throws ApplicationException
     */
    public function __construct(
        PathsStorage $paths,
        array        $customArgs = []
    ) {
        $this->paths = $paths;
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
            $conn = new PDO("sqlite:{$this->paths->root()}/{$_ENV['DB_PATH']}");

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
            $dotenv = Dotenv::createMutable($this->paths->root());
            $dotenv->load();
        } catch (Throwable $e) {
            throw ApplicationException::from($e, 'Couldn\'t find sqlite file.');
        }


        // This will override existing .env values.
        foreach ($customEnv as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $this->environment = $_ENV['APP_ENV'] ?? 'production';
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

    public function hasRoutingEnabled(): bool
    {
        return $this->paths->routesFile() !== null;
    }

    /**
     * **IMPORTANT** Must run after the Application is fully initialized.
     *
     * @throws ApplicationException
     */
    public function handleHttpRequests(): never
    {
        if(! $this->hasRoutingEnabled()) {
            $this->logger->critical("App doesn't have routing enabled but is attempting to handle HTTP requests.");
            echo "Error 500";
            exit(1);
        }

        $this->router = new Router(Request::make());

        // Loads in all `Route::` definitions.
        require_once $this->paths->routesFile();

        $this->logger->info("Router is listening for HTTP requests.");

        $response = $this->router->handleRequest();

        $response->send();
    }
}