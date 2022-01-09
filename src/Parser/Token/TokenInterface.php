<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

interface TokenInterface
{
    public function __construct(TokenType $type, string $value, int $line, int $offset);
}
