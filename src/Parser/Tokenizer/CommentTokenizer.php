<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
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
