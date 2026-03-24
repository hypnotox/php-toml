<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

/** @psalm-api */
interface TomlFactoryInterface
{
    public function make(): TomlInterface;

    public function fromString(string $input): TomlInterface;
}
