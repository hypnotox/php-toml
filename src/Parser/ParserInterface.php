<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\AbstractParserException;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\TomlInterface;

interface ParserInterface
{
    public const NEWLINE_TOKEN = [
        TokenType::T_RETURN,
        ...self::KEY_TOKEN,
    ];

    public const KEY_TOKEN = [
        TokenType::T_STRING,
        TokenType::T_INTEGER,
        TokenType::T_FLOAT,
        TokenType::T_BRACKET_OPEN,
    ];

    public const VALUE_TOKEN = [
        TokenType::T_STRING,
        TokenType::T_INTEGER,
        TokenType::T_FLOAT,
        TokenType::T_BRACKET_OPEN,
        TokenType::T_BOOLEAN,
        TokenType::T_DATETIME,
        TokenType::T_DATE,
        TokenType::T_TIME,
    ];

    /**
     * @throws AbstractParserException
     */
    public function parse(string $input): TomlInterface;
}
