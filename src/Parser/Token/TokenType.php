<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

enum TokenType
{
    // Characters
    case T_RETURN;
    case T_PUNCTUATION;
    case T_EQUALS;
    case T_BRACKET_OPEN;
    case T_BRACKET_CLOSE;
    case T_SINGLE_QUOTE;
    case T_DOUBLE_QUOTE;
    case T_COMMA;
    case T_DOT;

    // Values
    case T_INTEGER;
    case T_FLOAT;
    case T_STRING;
    case T_BOOLEAN;
    case T_DATETIME;
    case T_DATE;
    case T_TIME;
}
