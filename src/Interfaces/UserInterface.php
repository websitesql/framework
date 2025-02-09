<?php declare(strict_types=1);

namespace AlanTiller\Framework\Interfaces;

interface UserInterface
{
    public function find(int $id): ?object;
    public function findByCredentials(array $credentials): ?object;
    public function create(array $data): ?object;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}