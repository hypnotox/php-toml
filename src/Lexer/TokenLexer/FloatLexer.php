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
final class FloatLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_FLOAT;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        $floatLength = $stream->seekUntilNot($this->getTokenType());

        if (0 === $floatLength) {
            return false;
        }

        $float = $stream->peek($floatLength);

        return is_numeric($float);
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

    private function consumeStream(StreamInterface $stream): float
    {
        return (float) $stream->consumeUntil(
            [
                ...TokenType::T_WHITESPACE->getCharacters(),
                ...TokenType::T_NEWLINE->getCharacters(),
                ...TokenType::T_EQUALS->getCharacters(),
            ]
        );
    }
}
