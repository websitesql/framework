<?php declare(strict_types=1);

namespace AlanTiller\Framework\Interfaces;

interface RouterInterface
{
    public function addRoute(string $method, string $route, callable|string $handler): void;
    public function dispatch(): void;
}