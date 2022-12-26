<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

final class Token implements TokenInterface
{
    public function __construct(
        private readonly TokenType $type,
        private readonly mixed $value,
    ) {
    }

    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
