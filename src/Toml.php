<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

/**
 * @psalm-immutable
 */
final class Toml implements TomlInterface
{
    public function toArray(): array
    {
        return [];
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        // TODO: Implement JSON string representation according to TOML test suite
        return json_encode($this->toArray(), \JSON_THROW_ON_ERROR);
    }
}
