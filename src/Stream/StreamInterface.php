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

    /**
     * @param string[]|TokenType $characters
     */
    public function seekUntil(array|TokenType $characters): int;

    /**
     * @param string[]|TokenType $characters
     */
    public function seekUntilNot(array|TokenType $characters): int;

    public function consume(int $length = 1): string;

    /**
     * @param string[]|TokenType $characters
     */
    public function consumeUntil(array|TokenType $characters): string;

    /**
     * @param string[]|TokenType $characters
     */
    public function consumeUntilNot(array|TokenType $characters): string;

    public function isEndOfFile(): bool;
}
