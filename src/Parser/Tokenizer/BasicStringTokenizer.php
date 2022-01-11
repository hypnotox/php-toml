<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class BasicStringTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        $lineNumber = $seeker->getLineNumber();
        $lineOffset = $seeker->getLineOffset();
        $string = $seeker->peekUntilOneOf(['=', ',', '[', ']', SeekerInterface::COMMENT, SeekerInterface::EOL, ...SeekerInterface::WHITESPACE]);

        if (trim($string) === '') {
            return false;
        }

        $seeker->consume(\strlen($string));
        $tokenStream->addToken(
            $this->tokenFactory->make(
                TokenType::T_BASIC_STRING,
                trim($string),
                $lineNumber,
                $lineOffset,
            )
        );

        return true;
    }
}
