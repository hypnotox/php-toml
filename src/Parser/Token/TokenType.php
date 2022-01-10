<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

enum TokenType
{
    // Characters
    case T_RETURN;
    case T_PUNCTUATION;

    // Structures
    case T_KEY;
    case T_TABLE_HEAD;
    case T_ARRAY_START;
    case T_ARRAY_END;

    // Values
    case T_BASIC_STRING;
    case T_QUOTED_STRING;
    case T_LITERAL_STRING;
    case T_EQUALS;
    case T_BOOLEAN;
    case T_DATETIME;

    case T_COMMENT;
}
