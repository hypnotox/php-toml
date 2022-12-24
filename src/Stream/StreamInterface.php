<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

/**
 * @internal
 */
interface StreamInterface
{
    public function peek(int $length = 1): string;

    /**
     * @param string[] $characters
     */
    public function seekUntil(array $characters): int;

    /**
     * @param string[] $characters
     */
    public function seekUntilNot(array $characters): int;

    public function consume(int $length = 1): string;

    /**
     * @param string[] $characters
     */
    public function consumeUntil(array $characters): string;

    /**
     * @param string[] $characters
     */
    public function consumeUntilNot(array $characters): string;

    public function isEndOfFile(): bool;
}
