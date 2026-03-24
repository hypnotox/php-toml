<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

use Override;

/**
 * @internal
 *
 * @psalm-api
 */
final class Token implements TokenInterface
{
    public function __construct(
        private readonly TokenType $type,
        private readonly mixed $value,
        private readonly int $row,
        private readonly int $column,
    ) {
    }

    #[Override]
    public function getType(): TokenType
    {
        return $this->type;
    }

    #[Override]
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}
