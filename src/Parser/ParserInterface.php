<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\TomlException;
use HypnoTox\Toml\TomlInterface;

interface ParserInterface
{
    /**
     * @throws TomlException
     */
    public function parse(string $input): TomlInterface;
}
