<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;

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
