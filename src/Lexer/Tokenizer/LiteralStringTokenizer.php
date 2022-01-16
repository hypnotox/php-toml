<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function HypnoTox\Toml\Tokenizer\str_ends_with;
use function in_array;
use function strlen;

final class LiteralStringTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if ('\'' === $stream->peek() && '\'\'\'' !== $stream->peek(3)) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();
            $lastChar = null;
            $string = $stream->consume(
                strlen(
                    $stream->peekUntilCallback(
                        static function (string $char) use (&$lastChar) {
                            if ('\'' === $char && '\\\'' !== $lastChar) {
                                return true;
                            }

                            $lastChar = $char;

                            return !in_array($char, ['\'', StringStreamInterface::EOL], true);
                        },
                    ),
                ),
            );

            if (str_ends_with($string, StringStreamInterface::EOL)) {
                $this->raiseException($stream, 'Unexpected T_RETURN "\n", expected T_SINGLE_QUOTE "\'"');
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
