<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
use function strlen;

final class DatetimeTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        $string = $stream->peekUntilOneOf(['=', ',', '[', ']', StringStreamInterface::COMMENT, StringStreamInterface::EOL, ...StringStreamInterface::WHITESPACE]);
        $lineNumber = $stream->getLineNumber();
        $lineOffset = $stream->getLineOffset();

        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,6})?([+-]\d{2}:\d{2})?~', $string)) {
            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_DATETIME,
                    trim($stream->consume(strlen($string))),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if (preg_match('~^\d{4}-\d{2}-\d{2}~', $string)) {
            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_DATE,
                    trim($stream->consume(strlen($string))),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if (preg_match('~^\d{2}:\d{2}:\d{2}(\.\d{1,6})?([+-]\d{2}:\d{2})?~', $string)) {
            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_TIME,
                    trim($stream->consume(strlen($string))),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
