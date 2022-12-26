<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;

enum TokenType
{
    case T_NEWLINE;
    case T_COMMENT;
    case T_KEY;
    case T_QUOTED_KEY;
    case T_EQUALS;
    case T_FLOAT;
    case T_INTEGER;
    case T_BASIC_STRING;
    case T_QUOTED_STRING;
//    case T_FLOAT;
//    case T_BOOLEAN;
//    case T_DATETIME;
//    case T_ARRAY;
//    case T_TABLE;
//    case T_INLINE_TABLE;

    /**
     * @return self[]
     */
    public static function getDefaultTokens(): array
    {
        return [
            self::T_NEWLINE,
            self::T_COMMENT,
            self::T_QUOTED_KEY,
            self::T_KEY,
        ];
    }

    /**
     * @return TokenType[]
     */
    public static function getValueTokens(): array
    {
        return [
            self::T_FLOAT,
            self::T_INTEGER,
            self::T_BASIC_STRING,
            self::T_QUOTED_STRING,
        ];
    }

    public function matches(StringStreamInterface $stream): bool
    {
        return match ($this) {
            self::T_FLOAT => (function (StringStreamInterface $stream): bool {
                if (!$stream->matches($this)) {
                    return false;
                }

                $match = $stream->peekMatching($this);

                return (int) $match != (float) $match;
            })($stream),
            default => $stream->matches($this),
        };
    }

    public function getRegex(): string
    {
        return match ($this) {
            self::T_NEWLINE => '(\R)',
            self::T_COMMENT => '(#.*)',
            self::T_KEY, self::T_BASIC_STRING => '([a-zA-Z0-9_\-]+)',
            self::T_QUOTED_KEY, self::T_QUOTED_STRING => '("[\s\d\w\b"\\\.\']+")',
            self::T_EQUALS     => '(=)',
            self::T_FLOAT      => '([+-]?(\d+([.]\d*)?([eE][+-]?\d+)?|[.]\d+([eE][+-]?\d+)?))',
            self::T_INTEGER    => '([0-9]+)',
        };
    }

    /**
     * @return self[]
     */
    public function getExpectedTokens(): array
    {
        return match ($this) {
            self::T_KEY, self::T_QUOTED_KEY => [self::T_EQUALS],
            self::T_EQUALS => [...self::getValueTokens()],
            self::T_INTEGER, self::T_FLOAT, self::T_BASIC_STRING, self::T_QUOTED_STRING => [self::T_NEWLINE, self::T_COMMENT],
            default => self::getDefaultTokens(),
        };
    }
}
