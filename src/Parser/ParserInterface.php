<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\TomlInterface;

interface ParserInterface
{
    public function parse(string $input): TomlInterface;
}
