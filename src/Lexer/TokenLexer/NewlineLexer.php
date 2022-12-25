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
final class NewlineLexer implements TokenLexerInterface
{
    public function getTokenType(): TokenType
    {
        return TokenType::T_NEWLINE;
    }

    public function canTokenize(StreamInterface $stream): bool
    {
        return "\n" === $stream->peek() || "\r\n" === $stream->peek(2);
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
        if ("\r\n" === $stream->peek(2)) {
            return $stream->consume(2);
        }

        return $stream->consume();
    }
}
