<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Value;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;

final class BasicStringParser implements ValueParserInterface
{
    public function canHandle(TokenInterface $token): bool
    {
        return TokenType::T_BASIC_STRING === $token->getType();
    }

    public function parse(TokenStreamInterface $stream): string
    {
        return $stream->consume()->getValue();
    }
}
