<?php

namespace ProblemDetails;

use Exception;
use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * Class ProblemDetailsException
 *
 * This exception is used to represent an error condition with a Problem Details object.
 * It extends the base Exception class and includes a ProblemDetails object.
 */
class ProblemDetailsException extends Exception
{

    public function __construct(
        public ProblemDetails $problem_details,
        Throwable $previous = null
    ) {
        parent::__construct($problem_details->title, $problem_details->status, $previous);
    }

    public static function fromException(Exception $e, ?RequestInterface $request = null): self
    {
        $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;

        $problem_details = new ProblemDetails(
            type: 'about:blank',
            title: 'An error occurred',
            status: $status,
            detail: $e->getMessage(),
            instance: $request?->getUri()?->getPath() ?? $_SERVER['REQUEST_URI'] ?? null,
        );

        return new self($problem_details, $e);
    }
}
