<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class EndOfLineTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (StringStreamInterface::EOL === $stream->peek()) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_RETURN,
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
