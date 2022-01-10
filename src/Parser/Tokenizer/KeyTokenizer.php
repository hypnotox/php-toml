<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class KeyTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if (0 === $seeker->getLineOffset() && ('"' === $seeker->peek() || '\'' === $seeker->peek() || preg_match("~^\w$~", $seeker->peek()))) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();
            $key = trim($seeker->consume(\strlen($seeker->peekUntilOneOf(['=', '#', SeekerInterface::EOL]))));

            if (str_ends_with($key, SeekerInterface::EOL)) {
                $this->raiseException($seeker, 'Unexpected T_RETURN "\n", expected T_EQUALS "="');
            }

            if (str_ends_with($key, '#')) {
                $this->raiseException($seeker, 'Unexpected T_COMMENT "#", expected T_EQUALS "="');
            }

            if (str_starts_with($key, '"')) {
                if (!str_ends_with($key, '"')) {
                    $this->raiseException($seeker, 'Unexpected T_EQUALS "=", expected T_DOUBLE_QUOTE """');
                }

                $key = substr($key, 1, -1);
            }

            if (str_starts_with($key, '\'')) {
                if (!str_ends_with($key, '\'')) {
                    $this->raiseException($seeker, 'Unexpected T_EQUALS "=", expected T_SINGLE_QUOTE "\'"');
                }

                $key = substr($key, 1, -1);
            }

            if (str_ends_with($key, '"') && !str_ends_with($key, '\"')) {
                $this->raiseException($seeker, 'Unexpected T_DOUBLE_QUOTE """, expected T_EQUALS "="');
            }

            if (str_ends_with($key, '\'') && !str_ends_with($key, '\\\'')) {
                $this->raiseException($seeker, 'Unexpected T_SINGLE_QUOTE "\'", expected T_EQUALS "="');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_KEY,
                    $key,
                    $lineNumber,
                    $lineOffset,
                )
            );

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

        return false;
    }
}
