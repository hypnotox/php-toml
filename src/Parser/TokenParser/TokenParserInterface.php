<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser;

use HypnoTox\Toml\Lexer\Token\TokenInterface;

interface TokenParserInterface
{
    public function canHandle(TokenInterface $token): bool;
}
