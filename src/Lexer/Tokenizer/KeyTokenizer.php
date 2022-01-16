<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function strlen;

final class KeyTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (0 === $stream->getLineOffset()) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();
            $string = $stream->peekUntilOneOf(['=', ',', StringStreamInterface::EOL, StringStreamInterface::COMMENT]);
            $stream->consume(strlen($string));

            if (StringStreamInterface::EOL === $stream->peek()) {
                $this->raiseException($stream, 'Unexpected T_RETURN "\n", expected T_EQUALS "="');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_KEY,
                    trim($string),
                    $lineNumber,
                    $lineOffset,
                )
            );

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

        return false;
    }
}
