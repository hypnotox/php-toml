<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

interface StringStreamInterface extends StreamInterface
{
    public const WHITESPACE = [' ', "\t"];
    public const EOL = "\n";
    public const COMMENT = '#';

    public function __construct(string $input, int $lineNumber = 1, int $lineOffset = 0);

    public function getPointer(): int;

    public function getLineNumber(): int;

    public function incrementLineNumber(int $n = 1): void;

    public function getLineOffset(): int;

    /**
     * Returns $n characters without forwarding the pointer.
     */
    public function peek(int $n = 1): string;

    public function peekUntil(string $search): string;

    /**
     * @param string[] $search
     */
    public function peekUntilOneOf(array $search): string;

    public function peekUntilNotOneOf(array $search): string;

    public function peekUntilCallback(callable $callback): string;

    public function peekUntilWhitespace(): string;

    public function peekUntilEOS(): string;

    public function peekUntilEOL(): string;

    /**
     * Returns $n characters and forwards the pointer by $n.
     */
    public function consume(int $n = 1): string;

    public function consumeWhitespace(): void;

    public function isEOF(): bool;
}
