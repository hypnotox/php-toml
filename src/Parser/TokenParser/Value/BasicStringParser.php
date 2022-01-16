<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Value;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenType;

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
