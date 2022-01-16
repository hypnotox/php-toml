<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Token;

final class TokenFactory implements TokenFactoryInterface
{
    public function make(TokenType $type, string $value, int $line, int $offset): TokenInterface
    {
        return new Token($type, $value, $line, $offset);
    }
}
