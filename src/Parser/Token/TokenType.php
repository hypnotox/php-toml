<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

/**
 * @internal
 *
 * @psalm-api
 */
enum TokenType
{
    // Structure
    case T_LEFT_BRACKET;
    case T_RIGHT_BRACKET;
    case T_DOUBLE_LEFT_BRACKET;
    case T_DOUBLE_RIGHT_BRACKET;
    case T_LEFT_BRACE;
    case T_RIGHT_BRACE;
    case T_EQUALS;
    case T_DOT;
    case T_COMMA;

    // String values
    case T_BASIC_STRING;
    case T_LITERAL_STRING;
    case T_ML_BASIC_STRING;
    case T_ML_LITERAL_STRING;

    // Numeric values
    case T_INTEGER;
    case T_HEX_INTEGER;
    case T_OCT_INTEGER;
    case T_BIN_INTEGER;
    case T_FLOAT;
    case T_BOOL;

    // Datetime values
    case T_OFFSET_DATETIME;
    case T_LOCAL_DATETIME;
    case T_LOCAL_DATE;
    case T_LOCAL_TIME;

    // Keys
    case T_BARE_KEY;

    // Whitespace & control
    case T_NEWLINE;
    case T_WHITESPACE;
    case T_COMMENT;
    case T_EOF;
}
