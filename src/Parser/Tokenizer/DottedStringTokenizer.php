<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
use function strlen;

final class DottedStringTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        $lineNumber = $stream->getLineNumber();
        $lineOffset = $stream->getLineOffset();
        $string = $stream->peekUntilOneOf(['=', ',', '[', ']', StringStreamInterface::COMMENT, StringStreamInterface::EOL, ...StringStreamInterface::WHITESPACE]);

        if (!str_contains($string, '.')) {
            return false;
        }

        $stream->consume(strlen($string));
        $tokenStream->addToken(
            $this->tokenFactory->make(
                TokenType::T_DOTTED_STRING,
                trim($string),
                $lineNumber,
                $lineOffset,
            )
        );

        return true;
    }
}
