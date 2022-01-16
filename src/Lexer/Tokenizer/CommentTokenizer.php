<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function strlen;

final class CommentTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (StringStreamInterface::COMMENT === $stream->peek()) {
            $stream->consume(strlen($stream->peekUntilEOL()));

            return true;
        }

        return false;
    }
}
