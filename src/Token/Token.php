<?php

namespace HypnoTox\Toml\Token;

class Token implements TokenInterface
{
    public function __construct(
        private readonly TokenType $type,
        private readonly mixed $value,
    ) {
    }

    /**
     * @return TokenType
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
