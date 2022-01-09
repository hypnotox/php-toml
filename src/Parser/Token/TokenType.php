<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

enum TokenType: string
{
    case T_BASIC_STRING = 'basic-string';
    case T_QUOTED_STRING = 'quoted-string';
    case T_LITERAL_STRING = 'literal-string';
    case T_EQUALS = 'equals';
    case T_BOOLEAN = 'boolean';
    case T_TABLE_HEADER = 'table';
    case T_DATETIME = 'datetime';
    case T_ARRAY = 'array';
}
