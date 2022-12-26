<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

use HypnoTox\Toml\Parser\Token\TokenType;

/**
 * @internal
 */
interface StringStreamInterface
{
    public function peek(int $length = 1): string;

    public function peekMatching(string|TokenType $regex): string;

    public function consume(int $length = 1): string;

    public function consumeMatching(string|TokenType $regex): string;

    public function matches(string|TokenType $regex): bool;

    public function isEndOfFile(): bool;
}
