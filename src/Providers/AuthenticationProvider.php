<?php declare(strict_types=1);

namespace AlanTiller\Framework\Providers;

use AlanTiller\Framework\Interfaces\AuthenticationInterface;
use AlanTiller\Framework\Interfaces\UserInterface;
use AlanTiller\Framework\Core\Config;
use Psr\Log\LoggerInterface;

class AuthenticationProvider implements AuthenticationInterface
{
    private UserInterface $userProvider;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(UserInterface $userProvider, Config $config, LoggerInterface $logger)
    {
        $this->userProvider = $userProvider;
        $this->config = $config;
        $this->logger = $logger;

        session_start();
    }

    public function attempt(array $credentials): ?object
    {
        $user = $this->userProvider->findByCredentials($credentials);

        if ($user) {
            $_SESSION['user_id'] = $user->id;
            $this->logger->info('User logged in: ' . $user->id);
            return $user;
        }

        $this->logger->warning('Authentication failed for credentials: ' . json_encode($credentials));
        return null;
    }

    public function user(): ?object
    {
        if (isset($_SESSION['user_id'])) {
            return $this->userProvider->find($_SESSION['user_id']);
        }

        return null;
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']) && $this->user() !== null;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        session_destroy();
        $this->logger->info('User logged out.');
    }
}