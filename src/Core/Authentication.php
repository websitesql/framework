<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Interfaces\AuthenticationInterface;

class Authentication
{
    private AuthenticationInterface $auth;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    public function getAuth(): AuthenticationInterface
    {
        return $this->auth;
    }
}