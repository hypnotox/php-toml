<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
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
