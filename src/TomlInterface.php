<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use HypnoTox\Toml\Parser\TomlNode;
use HypnoTox\Toml\Parser\TomlTable;

/**
 * @psalm-immutable
 *
 * @psalm-api
 */
interface TomlInterface
{
    public function get(string $key): mixed;

    public function set(string $key, TomlNode $value): self;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    public function toJson(): string;

    public function getData(): TomlTable;

    public function toString(): string;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;
}
