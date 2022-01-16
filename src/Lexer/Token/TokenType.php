<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Token;

use const T_RETURN;
use const T_STRING;

enum TokenType
{
    // Characters
    case T_RETURN;
    case T_EQUALS;
    case T_BRACKET_OPEN;
    case T_BRACKET_CLOSE;
    case T_COMMA;
    // Structures
    case T_KEY;
    case T_BASIC_STRING;
    case T_LITERAL_STRING;
    // Values
    case T_INTEGER;
    case T_FLOAT;
    case T_STRING;
    case T_BOOLEAN;
    case T_DATETIME;
    case T_DATE;
    case T_TIME;
}
