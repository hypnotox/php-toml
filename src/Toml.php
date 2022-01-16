<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use const JSON_THROW_ON_ERROR;
use JsonException;

/**
 * @psalm-immutable
 */
final class Toml implements TomlInterface
{
    public function __construct(
        private readonly array $data = [],
    ) {
    }

    public function get(string $key): mixed
    {
        // TODO: Implement get() method.
    }

    public function set(string $key, mixed $value): self
    {
        $data = $this->data;

        // TODO: Evaluate key and set value.

        return new self($data);
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        // TODO: Implement JSON string representation according to TOML test suite
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
