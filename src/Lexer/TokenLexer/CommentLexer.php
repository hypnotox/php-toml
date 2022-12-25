<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\TokenLexer;

use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
final class CommentLexer extends AbstractTokenLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_COMMENT;
    }

    protected function consumeStream(StreamInterface $stream): string
    {
        return $stream->consumeUntil(TokenType::T_NEWLINE);
    }
}
