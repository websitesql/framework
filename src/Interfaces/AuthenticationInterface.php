<?php declare(strict_types=1);

namespace AlanTiller\Framework\Interfaces;

interface AuthenticationInterface
{
    public function attempt(array $credentials): ?object;
    public function user(): ?object;
    public function check(): bool;
    public function logout(): void;
}