<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use HypnoTox\Toml\Parser\Parser;
use Override;

final class TomlFactory implements TomlFactoryInterface
{
    #[Override]
    public function make(array $data = []): TomlInterface
    {
        return new Toml($data);
    }

    #[Override]
    public function fromString(string $input): TomlInterface
    {
        return (new Parser())->parse($input);
    }
}
