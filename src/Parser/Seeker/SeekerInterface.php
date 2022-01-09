<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

interface SeekerInterface
{
    public function __construct(string $input);

    public function getPointer(): int;

    public function getLineNumber(): int;

    public function incrementLineNumber(int $n = 1): void;

    public function getLineOffset(): int;

    public function getLineOffsetWithoutPrecedingWhitespace(): int;

    public function getLineWhitespaceLength(): int;

    /**
     * Returns $n characters without forwarding the pointer.
     */
    public function peek(int $n = 1): string;

    public function peekUntilEOL(): string;

    /**
     * Returns first match without forwarding the pointer.
     *
     * @return array<array{offset: int, length: int, value: string}>
     */
    public function seek(string $pattern): array;

    /**
     * Returns $n characters and forwards the pointer by $n.
     */
    public function consume(int $n = 1): string;

    public function consumeWhitespace(): void;

    public function consumeUntilEOL(): string;

    public function isEOF(): bool;
}
