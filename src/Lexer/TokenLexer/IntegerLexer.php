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
final class IntegerLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_INTEGER;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        $integerLength = $stream->seekUntilNot($this->getTokenType());

        if (0 === $integerLength) {
            return false;
        }

        $floatLength = $stream->seekUntilNot(TokenType::T_FLOAT);

        if ($integerLength !== $floatLength) {
            return false;
        }

        $integer = $stream->peek($integerLength);

        return is_numeric($integer);
    }

    public function tokenize(StreamInterface|string $input): array
    {
        $stream = $input instanceof StreamInterface ? $input : new Stream($input);

        return [
            new Token(
                $this->getTokenType(),
                $this->consumeStream($stream),
            ),
        ];
    }

    private function consumeStream(StreamInterface $stream): int
    {
        return (int) $stream->consumeUntil(
            [
                ...TokenType::T_WHITESPACE->getCharacters(),
                ...TokenType::T_NEWLINE->getCharacters(),
                ...TokenType::T_EQUALS->getCharacters(),
            ]
        );
    }
}
