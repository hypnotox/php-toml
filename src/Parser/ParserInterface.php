<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\ParserException;
use HypnoTox\Toml\TomlInterface;

interface ParserInterface
{
    /**
     * @throws ParserException
     */
    public function parse(string $input): TomlInterface;
}
