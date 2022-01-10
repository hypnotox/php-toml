<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class CommentTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if (SeekerInterface::COMMENT === $seeker->peek()) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_COMMENT,
                    $seeker->consume(\strlen($seeker->peekUntilEOL())),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
