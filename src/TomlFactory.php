<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

final class TomlFactory implements TomlFactoryInterface
{
    public function make(array $data = []): TomlInterface
    {
        return new Toml($data);
    }
}
