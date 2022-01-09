<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

/**
 * @psalm-immutable
 */
interface TomlInterface
{
    public function toArray(): array;

    public function toJson(): string;
}
