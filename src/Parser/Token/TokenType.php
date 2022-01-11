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

    // Values
    case T_BASIC_STRING;
    case T_QUOTED_STRING;
    case T_LITERAL_STRING;
    case T_DOTTED_STRING;
    case T_BOOLEAN;
    case T_DATETIME;
}
