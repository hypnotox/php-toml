<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

/**
 * @psalm-immutable
 */
interface TomlInterface
{
    public function __construct(array $data = []);

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): TomlInterface;

    public function toJson(): string;
}
