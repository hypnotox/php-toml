<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

final class Token implements TokenInterface
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $value,
        public readonly int $line,
        public readonly int $offset,
    ) {
    }

    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
