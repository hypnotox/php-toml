<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

enum LexerMode
{
    case LINE_START;
    case KEY;
    case VALUE;
    case LINE_END;
}
