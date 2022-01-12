<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class QuotedStringTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if ('"' === $stream->peek() && '"""' !== $stream->peek(3)) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();
            $lastChar = $stream->consume();
            $string = $stream->consume(
                \strlen(
                    $stream->peekUntilCallback(
                        function (string $char) use (&$lastChar) {
                            if ('"' === $char && '\\' === $lastChar) {
                                return true;
                            }

                            $lastChar = $char;

                            return !\in_array($char, ['"', StringStreamInterface::EOL], true);
                        },
                    ),
                ),
            );

            if (str_ends_with($string, StringStreamInterface::EOL)) {
                $this->raiseException($stream, 'Unexpected T_RETURN "\n", expected T_DOUBLE_QUOTE """');
            }

            if (str_ends_with($string, StringStreamInterface::COMMENT)) {
                $this->raiseException($stream, 'Unexpected T_COMMENT "#", expected T_DOUBLE_QUOTE """');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_QUOTED_STRING,
                    $string,
                    $lineNumber,
                    $lineOffset,
                )
            );

            $stream->consume();

            return true;
        }

        return false;
    }
}
