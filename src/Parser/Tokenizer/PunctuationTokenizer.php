<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class PunctuationTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        $char = $seeker->peek();

        if ($char === '=') {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_EQUALS,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if ($char === '[' && $seeker->getLineOffset() > 0) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_ARRAY_START,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if ($char === ']') {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_ARRAY_END,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        if (!\in_array($char, ['.', ','])) {
            return false;
        }

        $lineNumber = $seeker->getLineNumber();
        $lineOffset = $seeker->getLineOffset();

        $tokenStream->addToken(
            $this->tokenFactory->make(
                TokenType::T_PUNCTUATION,
                $seeker->consume(),
                $lineNumber,
                $lineOffset,
            )
        );

        return true;
    }
}
