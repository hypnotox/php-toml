<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;

/**
 * @internal
 */
enum TokenType
{
    case T_NEWLINE;
    case T_WHITESPACE;
    case T_COMMENT;
    case T_EOF;
    case T_KEY;
    case T_QUOTED_KEY;
    case T_DOT;
    case T_EQUALS;
    case T_FLOAT;
    case T_INTEGER;
    case T_BASIC_STRING;
    case T_QUOTED_STRING;

    public function matches(StringStreamInterface $stream): bool
    {
        return match ($this) {
            self::T_FLOAT => (function (StringStreamInterface $stream): bool {
                if (!$stream->matches($this)) {
                    return false;
                }

                $match = $stream->peekMatching($this);

                return (int) $match != (float) $match;
            }
            )(
                $stream
            ),
            self::T_EOF => $stream->isEndOfFile(),
            default => $stream->matches($this),
        };
    }

    public function getRegex(): string
    {
        return match ($this) {
            self::T_NEWLINE => '(\R)',
            self::T_WHITESPACE => '([ \t]+)',
            self::T_COMMENT => '(#.*)',
            self::T_EOF     => '$',
            self::T_KEY, self::T_BASIC_STRING => '([a-zA-Z0-9_\-]+)',
            self::T_QUOTED_KEY, self::T_QUOTED_STRING => '("[\s\d\w\b"\\\.\']+")',
            self::T_DOT     => '(\.)',
            self::T_EQUALS  => '(=)',
            self::T_FLOAT   => '([+-]?(\d+([.]\d*)?([eE][+-]?\d+)?|[.]\d+([eE][+-]?\d+)?))',
            self::T_INTEGER => '([0-9]+)',
        };
    }

    public function shouldAddToken(): bool
    {
        return match ($this) {
            self::T_EOF, self::T_WHITESPACE, self::T_COMMENT => false,
            default => true,
        };
    }
}
