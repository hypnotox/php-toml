<?php

namespace HypnoTox\Toml\Lexer\TokenLexer;

use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Token\Token;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
abstract class AbstractTokenLexer implements TokenLexerInterface
{
    abstract public function getTokenType(): TokenType;

    public function canTokenize(StreamInterface $stream): bool
    {
        return $stream->seekUntilNot($this->getTokenType()) > 0;
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

    abstract protected function consumeStream(StreamInterface $stream): mixed;
}
