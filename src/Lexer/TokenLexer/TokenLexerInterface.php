<?php

namespace HypnoTox\Toml\Lexer\TokenLexer;

use HypnoTox\Toml\Lexer\LexerInterface;
use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
interface TokenLexerInterface extends LexerInterface
{
    public function getTokenType(): TokenType;

    public function canTokenize(StreamInterface $stream): bool;
}
