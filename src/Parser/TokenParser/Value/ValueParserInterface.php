<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Value;

use HypnoTox\Toml\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\TokenParser\TokenParserInterface;

interface ValueParserInterface extends TokenParserInterface
{
    /**
     * @throws TomlExceptionInterface
     */
    public function parse(TokenStreamInterface $stream): mixed;
}
