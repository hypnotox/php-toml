<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Token;

use const T_ARRAY;
use const T_COMMENT;
use const T_STRING;
use const T_WHITESPACE;

enum TokenType
{
    case T_WHITESPACE;
    case T_NEWLINE;
    case T_COMMENT;
    case T_EOF;

//    case T_STRING;
//    case T_INTEGER;
//    case T_FLOAT;
//    case T_BOOLEAN;
//    case T_DATETIME;
//    case T_ARRAY;
//    case T_TABLE;
//    case T_INLINE_TABLE;

    /**
     * @return string[]
     */
    public function getCharacters(): array
    {
        return match ($this) {
            TokenType::T_WHITESPACE => [' ', "\t"],
            TokenType::T_NEWLINE => ["\n", "\r\n"],
            TokenType::T_COMMENT => ['#'],
            TokenType::T_EOF => [''],
        };
    }
}
