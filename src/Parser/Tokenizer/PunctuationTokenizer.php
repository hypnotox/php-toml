<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class PunctuationTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        $char = $stream->peek();

        if ('=' === $char) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_EQUALS,
                    $stream->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if ('[' === $char) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_BRACKET_OPEN,
                    $stream->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if (']' === $char) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_BRACKET_CLOSE,
                    $stream->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if (',' === $char) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_COMMA,
                    $stream->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
