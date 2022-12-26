<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use HypnoTox\Toml\Parser\Parser;

final class TomlFactory implements TomlFactoryInterface
{
    public function make(array $data = []): TomlInterface
    {
        return new Toml($data);
    }

    public function fromString(string $input): TomlInterface
    {
        return (new Parser())->parse($input);
    }
}
