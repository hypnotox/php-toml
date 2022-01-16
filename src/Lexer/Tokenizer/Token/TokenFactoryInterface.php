<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer\Token;

interface TokenFactoryInterface
{
    public function make(TokenType $type, string $value, int $line, int $offset): TokenInterface;
}
