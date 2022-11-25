<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Exception\AbstractParserException;
use HypnoTox\Toml\TomlInterface;

interface ParserInterface
{
    /**
     * @throws AbstractParserException
     */
    public function parse(string $input): TomlInterface;
}
