<?php

namespace ProblemDetails;

use Psr\Log\{LoggerInterface, NullLogger};
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use function is_string, json_encode, json_last_error, json_last_error_msg;

/**
 * Middleware that catches exceptions of type ProblemDetailsException and returns a
 * response with the appropriate problem details.
 */
readonly class ProblemDetailsMiddleware implements MiddlewareInterface
{

    public function __construct(
        private ResponseFactoryInterface $response_factory,
        private LoggerInterface $logger = new NullLogger(),
        private bool $add_instance_if_missing = true
    ) {}

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return (static fn(): ResponseInterface => $handler->handle($request))();
        } catch (ProblemDetailsException $exception) {
            $this->logger->info('ProblemDetailsException caught', [
                'exception' => $exception,
                'request' => $request
            ]);

            $response = $this
                ->response_factory
                ->createResponse($this->getStatusCodeFromException($exception))
                ->withHeader('Content-Type', 'application/problem+json');

            if ($this->add_instance_if_missing) {
                $exception->problem_details->instance ??= $request->getUri()->getPath();
            }

            $json = json_encode(
                $exception->problem_details,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
            );

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Failed to encode problem details to JSON', [
                    'error' => json_last_error_msg(),
                    'problem_details' => $exception->problem_details
                ]);
            }

            if (is_string($json)) {
                $response->getBody()->write($json);
            }

            return $response;
        }
    }

    private function getStatusCodeFromException(ProblemDetailsException $exception): int
    {
        $status_code = $exception->problem_details->status ?? 500;

        if ($status_code < 100 || $status_code > 599) {
            $this->logger->warning('Invalid status code in problem details, forced to default 500', [
                'status_code' => $status_code,
                'default_status_code' => 500
            ]);

            return 500;
        }

        return $status_code;
    }
}
