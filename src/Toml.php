<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

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
        // TODO: Implement set() method.
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        // TODO: Implement JSON string representation according to TOML test suite
        return json_encode($this->data, \JSON_THROW_ON_ERROR);
    }
}
