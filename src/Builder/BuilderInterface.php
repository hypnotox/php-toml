<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Builder;

use HypnoTox\Toml\TomlInterface;

interface BuilderInterface
{
    public function build(): TomlInterface;

    // TODO: Create interface for TOML builder
}
