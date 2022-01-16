<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function HypnoTox\Toml\Tokenizer\str_contains;
use function strlen;

final class IntegerTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        $lineNumber = $stream->getLineNumber();
        $lineOffset = $stream->getLineOffset();
        $string = $stream->peekUntilOneOf(['=', ',', '[', ']', StringStreamInterface::COMMENT, StringStreamInterface::EOL, ...StringStreamInterface::WHITESPACE]);

        if (str_contains($string, '.') || !is_numeric(trim($string))) {
            return false;
        }

        $stream->consume(strlen($string));
        $tokenStream->addToken(
            $this->tokenFactory->make(
                TokenType::T_INTEGER,
                trim($string),
                $lineNumber,
                $lineOffset,
            )
        );

        return true;
    }
}
