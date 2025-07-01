<?php

use ProblemDetails\{ProblemDetails, ProblemDetailsException};

covers(ProblemDetailsException::class);

it('can be instantiated with a ProblemDetails object', function () {
    $problem = new ProblemDetails(
        type: 'https://example.com/errors/not-found',
        title: 'Resource not found',
        status: 404
    );

    $exception = new ProblemDetailsException($problem);

    expect($exception)->toBeInstanceOf(ProblemDetailsException::class);
});

it('sets the exception message to the ProblemDetails title', function () {
    $problem = new ProblemDetails(
        type: 'https://example.com/errors/validation',
        title: 'Validation failed',
        status: 422
    );

    $exception = new ProblemDetailsException($problem);

    expect($exception->getMessage())->toBe('Validation failed');
});

it('sets the exception code to the ProblemDetails status', function () {
    $problem = new ProblemDetails(
        type: 'https://example.com/errors/unauthorized',
        title: 'Unauthorized access',
        status: 401
    );

    $exception = new ProblemDetailsException($problem);

    expect($exception->getCode())->toBe(401);
});

it('stores the original ProblemDetails object', function () {
    $problemDetails = new ProblemDetails(
        type: 'https://example.com/errors/out-of-credit',
        title: 'You do not have enough credit',
        status: 403,
        detail: 'Your current balance is 30, but that costs 50.'
    );

    $exception = new ProblemDetailsException($problemDetails);

    expect($exception->problem_details)->toBe($problemDetails);
});
