<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class CommentTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (StringStreamInterface::COMMENT === $stream->peek()) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_BASIC_STRING,
                    trim($stream->consume(\strlen($stream->peekUntilEOL()))),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
