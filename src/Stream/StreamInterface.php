<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
interface StreamInterface
{
    public function peek(int $length = 1): string;

    public function peekMatching(string|TokenType $regex): string;

    public function consume(int $length = 1): string;

    public function consumeMatching(string|TokenType $regex): string;

    public function matches(string|TokenType $regex): bool;

    public function isEndOfFile(): bool;
}
