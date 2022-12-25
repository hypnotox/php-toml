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
    case T_EQUALS;
    case T_DOUBLE_QUOTE;
    case T_SINGLE_QUOTE;
    case T_INTEGER;
    case T_FLOAT;
    case T_STRING;

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
        $lowercase = range('a', 'z');
        $uppercase = range('A', 'Z');
        $numbers = array_map(fn (int $number): string => (string) $number, range(0, 9));

        return match ($this) {
            TokenType::T_WHITESPACE => [' ', "\t"],
            TokenType::T_NEWLINE => ["\n", "\r\n"],
            TokenType::T_COMMENT => ['#'],
            TokenType::T_EOF => [],
            TokenType::T_EQUALS => ['='],
            TokenType::T_DOUBLE_QUOTE => ['"'],
            TokenType::T_SINGLE_QUOTE => ['\''],
            TokenType::T_INTEGER => $numbers,
            TokenType::T_FLOAT => [
                ...$numbers,
                ...mb_str_split('.+-eE'),
            ],
            TokenType::T_STRING => [
                ...$lowercase,
                ...$uppercase,
                ...$numbers,
                ...['_', '-'],
            ], // mb_str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-')
        };
    }
}
