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
}
