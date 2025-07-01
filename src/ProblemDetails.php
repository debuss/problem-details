<?php

namespace ProblemDetails;

use JsonSerializable;

/**
 * Represents a Problem Details object as defined in RFC 7807.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7807
 */
class ProblemDetails implements JsonSerializable
{

    public function __construct(
        public string $type,
        public string $title,
        public int $status,
        public ?string $detail = null,
        public ?string $instance = null,
        /** @var array<string, string|string[]> $extensions */
        public array $extensions = []
    ) {}

    /**
     * Serializes the Problem Details object to an array for JSON representation.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->detail,
            'instance' => $this->instance,
        ], $this->extensions);
    }
}
