<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class LiteralStringTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if ('\'' === $seeker->peek() && '\'\'\'' !== $seeker->peek(3)) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();
            $lastChar = null;
            $string = $seeker->consume(
                \strlen(
                    $seeker->peekUntilCallback(
                        function (string $char) use (&$lastChar) {
                            if ('\'' === $char && '\\\'' !== $lastChar) {
                                return true;
                            }

                            $lastChar = $char;

                            return !\in_array($char, ['\'', SeekerInterface::EOL, SeekerInterface::COMMENT], true);
                        },
                    ),
                ),
            );

            if (str_ends_with($string, SeekerInterface::EOL)) {
                $this->raiseException($seeker, 'Unexpected T_RETURN "\n", expected T_SINGLE_QUOTE "\'"');
            }

            if (str_ends_with($string, SeekerInterface::COMMENT)) {
                $this->raiseException($seeker, 'Unexpected T_COMMENT "#", expected T_SINGLE_QUOTE "\'"');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_LITERAL_STRING,
                    $string,
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
