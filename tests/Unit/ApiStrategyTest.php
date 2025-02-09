<?php

use AlanTiller\Framework\Strategy\ApiStrategy;
use Laminas\Diactoros\ResponseFactory;

it('creates error response', function () {
    $responseFactory = new ResponseFactory();
    $strategy = new ApiStrategy($responseFactory, 0, true);
    $response = $responseFactory->createResponse();
    $exception = new \Exception('Test error');

    $errorResponse = $strategy->createErrorResponse($response, $exception);

    expect($errorResponse->getStatusCode())->toBe(500);
    expect($errorResponse->getHeaderLine('Content-Type'))->toBe('application/json');

    $body = json_decode($errorResponse->getBody()->__toString(), true);
    expect($body)->toHaveKey('error');
    expect($body['error'])->toHaveKey('message');
    expect($body['error'])->toHaveKey('type');
    expect($body['error'])->toHaveKey('code');
    expect($body['error'])->toHaveKey('debug');
});