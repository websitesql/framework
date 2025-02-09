<?php declare(strict_types=1);

namespace AlanTiller\Framework\Providers;

use AlanTiller\Framework\Interfaces\UserInterface;
use AlanTiller\Framework\Core\Config;
use Psr\Log\LoggerInterface;
use Medoo\Medoo;

class UserProvider implements UserInterface
{
    private Medoo $database;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(Medoo $database, Config $config, LoggerInterface $logger)
    {
        $this->database = $database;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function find(int $id): ?object
    {
        $user = $this->database->get('users', '*', ['id' => $id]);

        if ($user) {
            return (object) $user;
        }

        return null;
    }

    public function findByCredentials(array $credentials): ?object
    {
        $usernameField = $this->config->get('auth.username_field', 'email');
        $passwordField = $this->config->get('auth.password_field', 'password');

        $user = $this->database->get('users', '*', [
            $usernameField => $credentials['username']
        ]);

        if ($user && password_verify($credentials['password'], $user[$passwordField])) {
            return (object) $user;
        }

        return null;
    }

    public function create(array $data): ?object
    {
        $passwordField = $this->config->get('auth.password_field', 'password');
        $data[$passwordField] = password_hash($data[$passwordField], PASSWORD_DEFAULT);

        $id = $this->database->insert('users', $data);

        if ($id) {
            $this->logger->info('User created with ID: ' . $id);
            return $this->find($id);
        }

        $this->logger->error('Failed to create user.');
        return null;
    }

    public function update(int $id, array $data): bool
    {
        $passwordField = $this->config->get('auth.password_field', 'password');
        if (isset($data[$passwordField])) {
            $data[$passwordField] = password_hash($data[$passwordField], PASSWORD_DEFAULT);
        }

        $result = $this->database->update('users', $data, ['id' => $id]);

        if ($result->rowCount() > 0) {
            $this->logger->info('User updated with ID: ' . $id);
            return true;
        }

        $this->logger->warning('Failed to update user with ID: ' . $id);
        return false;
    }

    public function delete(int $id): bool
    {
        $result = $this->database->delete('users', ['id' => $id]);

        if ($result->rowCount() > 0) {
            $this->logger->info('User deleted with ID: ' . $id);
            return true;
        }

        $this->logger->warning('Failed to delete user with ID: ' . $id);
        return false;
    }
}