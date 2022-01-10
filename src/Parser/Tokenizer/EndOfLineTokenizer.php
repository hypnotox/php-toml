<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class EndOfLineTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if (SeekerInterface::EOL === $seeker->peek()) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_RETURN,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
