<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;

final class CommentTokenizer extends AbstractTokenizer
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (StringStreamInterface::COMMENT === $stream->peek()) {
            // Just consume comments without adding a token
            $stream->consume(\strlen($stream->peekUntilEOL()));

            return true;
        }

        return false;
    }
}
