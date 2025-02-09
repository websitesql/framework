<?php declare(strict_types=1);

namespace AlanTiller\Framework\Interfaces;

interface UtilitiesInterface
{
    public function generateRandomString(int $length = 16): string;
    public function hashPassword(string $password): string;
    public function verifyPassword(string $password, string $hash): bool;
    public function slugify(string $string): string;
}