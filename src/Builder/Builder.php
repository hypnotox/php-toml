<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Builder;

use HypnoTox\Toml\Toml;
use HypnoTox\Toml\TomlInterface;

class Builder implements BuilderInterface
{
    public function build(): TomlInterface
    {
        return new Toml();
    }
}
