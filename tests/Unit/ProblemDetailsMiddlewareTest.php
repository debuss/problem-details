<?php

use ProblemDetails\{ProblemDetails, ProblemDetailsException, ProblemDetailsMiddleware};
use Psr\Http\Message\{ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
    StreamInterface,
    UriInterface};
use Psr\Http\Server\RequestHandlerInterface;

covers(ProblemDetailsMiddleware::class);

it('passes the request to the handler and returns its response when no exception occurs', function () {
    // Create mock objects
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);

    // Set expectations
    $handler->shouldReceive('handle')
        ->once()
        ->with($request)
        ->andReturn($response);

    // Create middleware and process request
    $middleware = new ProblemDetailsMiddleware($responseFactory);
    $result = $middleware->process($request, $handler);

    expect($result)->toBe($response);
});

it('catches ProblemDetailsException and returns a properly formatted response', function () {
    // Create mock objects
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $stream = Mockery::mock(StreamInterface::class);

    // Create problem details and exception
    $problemDetails = new ProblemDetails(
        type: 'https://example.com/errors/validation',
        title: 'Validation failed',
        status: 422,
        detail: 'The email field is required.'
    );

    $exception = new ProblemDetailsException($problemDetails);

    // Set expectations
    $handler->shouldReceive('handle')
        ->once()
        ->with($request)
        ->andThrow($exception);

    $responseFactory->shouldReceive('createResponse')
        ->once()
        ->with(422)
        ->andReturn($response);

    $response->shouldReceive('withHeader')
        ->once()
        ->with('Content-Type', 'application/problem+json')
        ->andReturn($response);

    $response->shouldReceive('getBody')
        ->once()
        ->andReturn($stream);

    $stream->shouldReceive('write')
        ->once()
        ->with(Mockery::on(function ($json) use ($problemDetails) {
            $decoded = json_decode($json, true);
            return
                $decoded['type'] === $problemDetails->type &&
                $decoded['title'] === $problemDetails->title &&
                $decoded['status'] === $problemDetails->status &&
                $decoded['detail'] === $problemDetails->detail;
        }));

    // Process request
    $middleware = new ProblemDetailsMiddleware($responseFactory, add_instance_if_missing: false);
    $result = $middleware->process($request, $handler);

    expect($result)->toBe($response);
});

it('add the missing instance', function () {
    // Create mock objects
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $stream = Mockery::mock(StreamInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    // Create problem details and exception
    $problemDetails = new ProblemDetails(
        type: 'https://example.com/errors/validation',
        title: 'Validation failed',
        status: 422,
        detail: 'The email field is required.'
    );

    $exception = new ProblemDetailsException($problemDetails);

    // Set expectations
    $handler->shouldReceive('handle')
        ->once()
        ->with($request)
        ->andThrow($exception);

    $responseFactory->shouldReceive('createResponse')
        ->once()
        ->with(422)
        ->andReturn($response);

    $response->shouldReceive('withHeader')
        ->once()
        ->with('Content-Type', 'application/problem+json')
        ->andReturn($response);

    $response->shouldReceive('getBody')
        ->once()
        ->andReturn($stream);

    $request->shouldReceive('getUri')
        ->once()
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->once()
        ->andReturn('/api/resource');

    $stream->shouldReceive('write')
        ->once()
        ->with(Mockery::on(function ($json) use ($problemDetails) {
            $decoded = json_decode($json, true);
            return
                $decoded['type'] === $problemDetails->type &&
                $decoded['title'] === $problemDetails->title &&
                $decoded['status'] === $problemDetails->status &&
                $decoded['detail'] === $problemDetails->detail &&
                isset($decoded['instance']) &&
                $decoded['instance'] === '/api/resource';
        }));

    // Process request
    $middleware = new ProblemDetailsMiddleware($responseFactory);
    $result = $middleware->process($request, $handler);

    expect($result)->toBe($response);
});
