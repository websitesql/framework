<?php declare(strict_types=1);

namespace AlanTiller\Framework\Providers;

use AlanTiller\Framework\Interfaces\UtilitiesInterface;

class UtilitiesProvider implements UtilitiesInterface
{
    public function generateRandomString(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function slugify(string $string): string
    {
        $string = strtolower(trim($string));
        $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return $string;
    }
}