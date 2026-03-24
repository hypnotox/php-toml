<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

/**
 * @internal
 */
enum LexerContext
{
    /** Expecting key, [, [[, comment, whitespace, newline, or EOF */
    case LineStart;

    /** Expecting bare key or quoted key */
    case Key;

    /** Expecting =, ., or whitespace after a key */
    case AfterKey;

    /** Expecting any value token */
    case Value;

    /** Like Value, but also expects ], comma, newline, comment */
    case ArrayItem;

    /** Expecting key or } in an inline table */
    case InlineTable;

    /** Expecting , or } after a value in an inline table */
    case InlineTableAfterValue;
}
