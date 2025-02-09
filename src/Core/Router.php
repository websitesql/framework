<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Interfaces\RouterInterface;

class Router
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }
}