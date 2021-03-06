<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer\Value;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function in_array;
use function strlen;

final class BasicStringTokenizer extends AbstractValueTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if ('"' === $stream->peek() && '"""' !== $stream->peek(3)) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();
            $lastChar = $stream->consume();
            $string = $stream->consume(
                strlen(
                    $stream->peekUntilCallback(
                        static function (string $char) use (&$lastChar) {
                            if ('"' === $char && '\\' === $lastChar) {
                                return true;
                            }

                            $lastChar = $char;

                            return !in_array($char, ['"', StringStreamInterface::EOL], true);
                        },
                    ),
                ),
            );

            if (str_ends_with($string, StringStreamInterface::EOL)) {
                $this->raiseException($stream, 'Unexpected T_RETURN "\n", expected T_DOUBLE_QUOTE """');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_BASIC_STRING,
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
