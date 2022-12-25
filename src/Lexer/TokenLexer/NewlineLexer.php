<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\TokenLexer;

use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
final class NewlineLexer extends AbstractTokenLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_NEWLINE;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        return "\n" === $stream->peek() || "\r\n" === $stream->peek(2);
    }

    protected function consumeStream(StreamInterface $stream): string
    {
        if ("\r\n" === $stream->peek(2)) {
            return $stream->consume(2);
        }

        return $stream->consume();
    }
}
