# Problem Details

An implementation of [RFC 7807](https://tools.ietf.org/html/rfc7807) - Problem Details for HTTP APIs.

This package provides a simple way to return standardized error responses that are both machine-readable and human-friendly.

## Installation

```bash
composer require debuss-a/problem-details
```

## Usage

### Creating a Problem Details object

```php
use ProblemDetails\ProblemDetails;

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

// JSON serialization
$json = json_encode($problem);
```

### Using the ProblemDetailsException

```php
use ProblemDetails\ProblemDetailsException;

try {
    // Your application logic
    if ($user->balance < $item->price) {
        $problem = new ProblemDetails(
            type: 'https://example.com/probs/out-of-credit',
            title: 'You do not have enough credit.',
            status: 403,
            detail: "Your current balance is {$user->balance}, but that costs {$item->price}."
        );
        
        throw new ProblemDetailsException($problem);
    }
} catch (ProblemDetailsException $e) {
    // Deal with the exception, or...
    // ...if set, the middleware will handle this automatically
    throw $e;
}
```

### Using the Middleware

Add the middleware to your middleware pipeline:

```php
use Laminas\Diactoros\ResponseFactory;
use ProblemDetails\ProblemDetailsMiddleware;

// For PSR-15 compatible frameworks
$response_factory = new ResponseFactory();
$app->add(new ProblemDetailsMiddleware($response_factory));
```

The middleware will:
1. Catch any `ProblemDetailsException`
2. Convert it to a proper Problem Details response
3. Set appropriate Content-Type header (`application/problem+json`)

## About RFC 7807

[RFC 7807](https://tools.ietf.org/html/rfc7807) defines a standard format for returning error details from HTTP APIs.  
It improves error handling by providing structured data that can be easily parsed by both machines and humans.

A Problem Details object includes:
- `type` - A URI reference that identifies the problem type
- `title` - A short, human-readable summary of the problem type
- `status` - The HTTP status code
- `detail` - A human-readable explanation specific to this occurrence of the problem
- `instance` - A URI reference that identifies the specific occurrence of the problem

## License

This package is released under the MIT License. See the LICENSE file for details.