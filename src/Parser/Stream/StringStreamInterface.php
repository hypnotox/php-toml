<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

/**
 * @internal
 *
 * @psalm-api
 */
interface StringStreamInterface
{
    public function getPointer(): int;

    public function getLine(): int;

    public function getColumn(): int;

    public function peek(int $length = 1): string;

    public function peekMatching(string $regex): string;

    public function consume(int $length = 1): string;

    public function consumeMatching(string $regex): string;

    public function matches(string $regex): bool;

    public function isEndOfFile(): bool;

    /**
     * Save the current stream position for backtracking.
     *
     * @return array{int, int, int} Saved state: [pointer, line, column]
     */
    public function save(): array;

    /**
     * Restore a previously saved stream position.
     *
     * @param array{int, int, int} $state State from save()
     */
    public function restore(array $state): void;
}
