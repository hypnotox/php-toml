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
final class StringLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_STRING;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        $stringLength = $stream->seekUntilNot($this->getTokenType());

        if (0 === $stringLength) {
            return false;
        }

        $string = $stream->peek($stringLength);

        return !is_numeric($string);
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

    private function consumeStream(StreamInterface $stream): string
    {
        return $stream->consumeUntil(
            [
                ...TokenType::T_WHITESPACE->getCharacters(),
                ...TokenType::T_NEWLINE->getCharacters(),
                ...TokenType::T_EQUALS->getCharacters(),
            ]
        );
    }
}
