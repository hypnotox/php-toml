<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\TokenLexer;

use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Token\Token;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
final class EqualsLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_EQUALS;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        return '=' === $stream->peek();
    }

    public function tokenize(StreamInterface|string $input): array
    {
        $stream = $input instanceof StreamInterface ? $input : new Stream($input);

        return [
            new Token(
                $this->getTokenType(),
                $stream->consume(),
            ),
        ];
    }
}
