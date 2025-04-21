<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Router\Factory;

use Laminas\Diactoros\ResponseFactory as LaminasResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    private ResponseFactoryInterface $factory;

    public function __construct()
    {
        $this->factory = new LaminasResponseFactory();
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->factory->createResponse($code, $reasonPhrase);
    }
}