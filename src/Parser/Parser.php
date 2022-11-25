<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\TomlInterface;

final class Parser implements ParserInterface
{
    public function parse(string $input): TomlInterface
    {
        dd($input);
    }
}
