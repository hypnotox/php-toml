<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use HypnoTox\Toml\Parser\Parser;
use Override;

/** @psalm-api */
final class TomlFactory implements TomlFactoryInterface
{
    #[Override]
    public function make(): TomlInterface
    {
        return new Toml();
    }

    #[Override]
    public function fromString(string $input): TomlInterface
    {
        return (new Parser())->parse($input);
    }
}
