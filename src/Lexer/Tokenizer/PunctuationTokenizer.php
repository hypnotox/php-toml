<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;

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
