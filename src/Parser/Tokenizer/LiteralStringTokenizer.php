<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
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
