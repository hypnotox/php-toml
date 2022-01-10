<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class TableHeadTokenizer extends AbstractTokenizer
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if (0 === $seeker->getLineOffset() && '[' === $seeker->peek()) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();
            $tableName = $seeker->consume(\strlen($seeker->peekUntilOneOf([']', '#', SeekerInterface::EOL])) + 1);

            if (str_ends_with($tableName, SeekerInterface::EOL)) {
                $this->raiseException($seeker, 'Unexpected T_RETURN "\n", expected T_BRACKET_CLOSE "]"');
            }

            if (str_ends_with($tableName, '#')) {
                $this->raiseException($seeker, 'Unexpected T_COMMENT "#", expected T_BRACKET_CLOSE "]"');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_TABLE_HEAD,
                    $tableName,
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
