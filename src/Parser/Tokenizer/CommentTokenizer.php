<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;

final class CommentTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if (SeekerInterface::COMMENT === $seeker->peek()) {
            // Just consume comments without adding a token
            $seeker->consume(\strlen($seeker->peekUntilEOL()));

            return true;
        }

        return false;
    }
}
