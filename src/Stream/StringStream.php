<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

use function in_array;
use function strlen;

final class StringStream implements StringStreamInterface
{
    private int $pointer = 0;
    private readonly int $inputLength;

    public function __construct(
        private string $input,
        private int $lineNumber = 1,
        private int $lineOffset = 0,
    ) {
        $this->inputLength = strlen($this->input);
        $this->consumeWhitespace();
    }

    public function getInputLength(): int
    {
        return $this->inputLength;
    }

    public function getPointer(): int
    {
        return $this->pointer;
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function incrementLineNumber(int $n = 1): void
    {
        if (0 === $n) {
            return;
        }

        $this->lineNumber += $n;
        $this->consumeWhitespace();
        $this->lineOffset = 0;
    }

    public function getLineOffset(): int
    {
        return $this->lineOffset;
    }

    public function peek(int $n = 1): string
    {
        return substr($this->input, $this->pointer, $n);
    }

    public function peekUntil(string $search, bool $inclusive = false): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while ($pointer < $this->inputLength && ($char = $this->input[$pointer]) !== '' && $char !== $search) {
            $buffer .= $char;
            ++$pointer;
        }

        if ($inclusive && $pointer < $this->inputLength) {
            $buffer .= $this->input[$pointer];
        }

        return $buffer;
    }

    public function peekUntilOneOf(array $search, bool $inclusive = false): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while ($pointer < $this->inputLength && ($char = $this->input[$pointer]) !== '' && !in_array($char, $search, true)) {
            $buffer .= $char;
            ++$pointer;
        }

        if ($inclusive && $pointer < $this->inputLength) {
            $buffer .= $this->input[$pointer];
        }

        return $buffer;
    }

    public function peekUntilNotOneOf(array $search, bool $inclusive = false): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while ($pointer < $this->inputLength && ($char = $this->input[$pointer]) !== '' && in_array($char, $search, true)) {
            $buffer .= $char;
            ++$pointer;
        }

        if ($inclusive && $pointer < $this->inputLength) {
            $buffer .= $this->input[$pointer];
        }

        return $buffer;
    }

    public function peekUntilCallback(callable $callback, bool $inclusive = false): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while ($pointer < $this->inputLength && ($char = $this->input[$pointer]) !== '' && $callback($char)) {
            $buffer .= $char;
            ++$pointer;
        }

        if ($inclusive && $pointer < $this->inputLength) {
            $buffer .= $this->input[$pointer];
        }

        return $buffer;
    }

    public function peekUntilEOS(): string
    {
        return $this->peekUntilOneOf([self::EOL, '#']);
    }

    public function peekUntilEOL(): string
    {
        return $this->peekUntil(self::EOL);
    }

    public function consume(int $n = 1): string
    {
        $subString = $this->peek($n);
        $this->forward($n);
        $this->incrementLineNumber(strlen($subString) - strlen(str_replace("\n", '', $subString)));

        return $subString;
    }

    public function consumeWhitespace(): void
    {
        $string = $this->peekUntilNotOneOf(self::WHITESPACE);
        $this->consume(strlen($string));
    }

    public function isEOF(): bool
    {
        return $this->pointer === $this->inputLength;
    }

    private function forward(int $n): void
    {
        $this->pointer += $n;
        $this->lineOffset += $n;
    }
}
