<?php

use ProblemDetails\ProblemDetails;

covers(ProblemDetails::class);

it('serializes problem details to JSON', function () {
    $problem = new ProblemDetails(
        type: 'https://example.com/probs/out-of-credit',
        title: 'You do not have enough credit.',
        status: 403,
        detail: 'Your current balance is 30, but that costs 50.',
        instance: '/account/12345/msgs/abc',
        extensions: [
            'balance' => 30,
            'accounts' => ['/account/12345', '/account/67890']
        ]
    );

    expect(json_encode($problem))->toBeJson()
        ->and(json_encode($problem))->toBe(json_encode([
            'type' => 'https://example.com/probs/out-of-credit',
            'title' => 'You do not have enough credit.',
            'status' => 403,
            'detail' => 'Your current balance is 30, but that costs 50.',
            'instance' => '/account/12345/msgs/abc',
            'balance' => 30,
            'accounts' => ['/account/12345', '/account/67890']
        ]))
        ->and($problem->type)->toBe('https://example.com/probs/out-of-credit')
        ->and($problem->title)->toBe('You do not have enough credit.')
        ->and($problem->status)->toBe(403)
        ->and($problem->detail)->toBe('Your current balance is 30, but that costs 50.')
        ->and($problem->instance)->toBe('/account/12345/msgs/abc')
        ->and($problem->extensions)->toBe([
            'balance' => 30,
            'accounts' => ['/account/12345', '/account/67890']
        ]);
});
