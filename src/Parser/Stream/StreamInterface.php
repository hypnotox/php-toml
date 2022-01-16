<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

interface StreamInterface
{
    public function getPointer(): int;

    /**
     * Returns the next element without forwarding the pointer.
     */
    public function peek(): mixed;

    /**
     * Returns the next element and forwards the pointer by 1.
     */
    public function consume(): mixed;

    public function isEOF(): bool;
}
