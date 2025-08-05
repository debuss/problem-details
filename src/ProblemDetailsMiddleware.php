<?php

namespace ProblemDetails;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Middleware that catches exceptions of type ProblemDetailsException and returns a
 * response with the appropriate problem details.
 */
readonly class ProblemDetailsMiddleware implements MiddlewareInterface
{

    public function __construct(
        private ResponseFactoryInterface $response_factory,
        private bool $add_instance_if_missing = true
    ) {}

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ProblemDetailsException $exception) {
            $response = $this
                ->response_factory
                ->createResponse($exception->problem_details->status)
                ->withHeader('Content-Type', 'application/problem+json');

            if ($this->add_instance_if_missing && empty($exception->problem_details->instance)) {
                $exception->problem_details->instance = $request->getUri()->getPath();
            }

            $json = json_encode($exception->problem_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if (is_string($json)) {
                $response->getBody()->write($json);
            }

            return $response;
        }
    }
}
