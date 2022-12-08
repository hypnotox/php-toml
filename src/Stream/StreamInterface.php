<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

/**
 * @template TStream
 * @template TUnit
 */
interface StreamInterface
{
    /**
     * @return TUnit
     */
    public function peek(int $length = 1): mixed;

    /**
     * @return TUnit
     */
    public function consume(int $length = 1): mixed;

    /**
     * @param TUnit[] $seekUnits
     */
    public function seekUntil(array $seekUnits): int;
}
