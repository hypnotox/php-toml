<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Value;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Parser\TokenParser\TokenParserInterface;

interface ValueParserInterface extends TokenParserInterface
{
    /**
     * @throws TomlExceptionInterface
     */
    public function parse(TokenStreamInterface $stream): mixed;
}
