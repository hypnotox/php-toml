<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

interface TokenInterface
{
    public function getType(): TokenType;

    public function getValue(): mixed;
}
