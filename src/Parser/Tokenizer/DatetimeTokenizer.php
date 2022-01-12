<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class DatetimeTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        $string = $seeker->peekUntilOneOf(['=', ',', '[', ']', SeekerInterface::COMMENT, SeekerInterface::EOL, ...SeekerInterface::WHITESPACE]);
        $lineNumber = $seeker->getLineNumber();
        $lineOffset = $seeker->getLineOffset();

        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,6})?([+-]\d{2}:\d{2})?~', $string)) {
            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_DATETIME,
                    trim($seeker->consume(\strlen($string))),
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
                    trim($seeker->consume(\strlen($string))),
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
                    trim($seeker->consume(\strlen($string))),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
