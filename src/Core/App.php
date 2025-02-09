<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Interfaces\RouterInterface;
use AlanTiller\Framework\Interfaces\MailInterface;
use AlanTiller\Framework\Interfaces\AuthenticationInterface;
use AlanTiller\Framework\Interfaces\UserInterface;
use AlanTiller\Framework\Interfaces\UtilitiesInterface;
use AlanTiller\Framework\Exceptions\ConfigurationException;
use AlanTiller\Framework\Exceptions\DatabaseException;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use AlanTiller\Framework\Core\Config;
use AlanTiller\Framework\Providers\LeagueRouteRouterProvider;
use AlanTiller\Framework\Providers\MailProvider;
use AlanTiller\Framework\Strategy\ApiStrategy;
use Laminas\Diactoros\ResponseFactory;
use AlanTiller\Framework\Providers\AuthenticationProvider;
use AlanTiller\Framework\Providers\UserProvider;
use AlanTiller\Framework\Providers\UtilitiesProvider;
use Medoo\Medoo;

class App
{
    private bool $initialised = false;
    private string $basePath;
    private Config $config;
    private ?Medoo $database = null;
    private ?RouterInterface $router = null;
    private ?MailInterface $mail = null;
    private ?AuthenticationInterface $auth = null;
    private ?UserInterface $user = null;
    private ?UtilitiesInterface $utilities = null;
    private ?LoggerInterface $logger = null;

    public function __construct(string $basePath = null, LoggerInterface $logger = null)
    {
        $this->basePath = $basePath ?? realpath(__DIR__ . '/../..');
        $this->logger = $logger ?? new NullLogger(); // Use a NullLogger if none provided
    }

    public function init(array $config = []): void
    {
        if ($this->initialised) {
            throw new \Exception('Application already initialised');
        }

        $this->initConfig($config);
        $this->initEnvironment();
        $this->initDatabase();
        $this->initSecurity();
        $this->initTimezone();
        $this->initialiseRouter();
        $this->initialiseMail();
        $this->initialiseAuthentication();
        $this->initialiseUtilities();

        $this->initialised = true;
    }

    private function initConfig(array $config): void
    {
        $this->config = new Config($this->basePath . '/config', $config);
    }

    private function initEnvironment(): void
    {
        $dotenv = Dotenv::createArrayBacked($this->basePath);
        $dotenv->load();

        $this->config->set('env', $_ENV);
    }

    private function initDatabase(): void
    {
        $dbConfig = $this->config->get('database');

        if (empty($dbConfig)) {
            throw new ConfigurationException('Database configuration not found.');
        }

        try {
            $this->database = new Medoo($dbConfig);
        } catch (\Exception $e) {
            throw new DatabaseException('Database connection error: ' . $e->getMessage());
        }
    }

    private function initSecurity(): void
    {
        header_remove('X-Powered-By');
    }

    private function initTimezone(): void
    {
        $timezone = $this->config->get('app.timezone', 'UTC');
        date_default_timezone_set($timezone);
    }

    public function initialiseRouter(RouterInterface $router = null): void
    {
        $corsConfig = $this->config->get('cors', []);
        $debug = $this->config->get('app.debug', false);
        $responseFactory = new ResponseFactory();

        $apiStrategy = new ApiStrategy($responseFactory, 0, $debug);
        $apiStrategy->corsConfig($corsConfig);

        if ($router === null) {
            $router = new LeagueRouteRouterProvider($apiStrategy);
        }

        $this->router = $router;
    }

    public function initialiseMail(MailInterface $mail = null): void
    {
        $mailConfig = $this->config->get('mail');

        if ($mail === null) {
            $mail = new MailProvider($mailConfig, $this->logger);
        }

        $this->mail = $mail;
    }

    public function initialiseAuthentication(): void
    {
        $userProvider = $this->getUserProvider();
        $auth = new AuthenticationProvider($userProvider, $this->config, $this->logger);
        $this->auth = $auth;
    }

    public function initialiseUtilities(): void
    {
        $this->utilities = new UtilitiesProvider();
    }

    public function serve(): void
    {
        if (!$this->initialised) {
            $this->init();
        }

        error_reporting(0);
        ini_set('display_errors', '0');

        if (!$this->router) {
            throw new \Exception('Router not initialised');
        }

        $this->router->dispatch();
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDatabase(): ?Medoo
    {
        return $this->database;
    }

    public function getRouter(): ?RouterInterface
    {
        return $this->router;
    }

    public function getMail(): ?MailInterface
    {
        return $this->mail;
    }

    public function getAuth(): ?AuthenticationInterface
    {
        return $this->auth;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getUtilities(): ?UtilitiesInterface
    {
        return $this->utilities;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    private function getUserProvider(): UserInterface
    {
        if ($this->user === null) {
            $this->user = new UserProvider($this->getDatabase(), $this->config, $this->logger);
        }
        return $this->user;
    }
}