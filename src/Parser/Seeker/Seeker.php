<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

final class Seeker implements SeekerInterface
{
    private int $pointer = 0;
    private int $inputLength;
    private int $lineNumber = 1;
    private int $lineOffset = 0;

    public function __construct(
        private string $input,
    ) {
        $this->inputLength = \strlen($this->input);
        $this->consumeWhitespace();
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

    public function peekUntil(string $search, int $skip = 0): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while (($char = $this->input[$pointer + $skip]) && $char !== $search) {
            $buffer .= $char;
            ++$pointer;
        }

        return $buffer;
    }

    public function peekUntilOneOf(array $search, int $skip = 0): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while (($char = $this->input[$pointer + $skip]) !== '' && !\in_array($char, $search, true)) {
            $buffer .= $char;
            ++$pointer;
        }

        return $buffer;
    }

    public function peekUntilNotOneOf(array $search, int $skip = 0): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while (($char = $this->input[$pointer + $skip]) && \in_array($char, $search, true)) {
            $buffer .= $char;
            ++$pointer;
        }

        return $buffer;
    }

    public function peekUntilCallback(callable $callback): string
    {
        $buffer = '';
        $pointer = $this->pointer;

        while (($char = $this->input[$pointer]) && $callback($char)) {
            $buffer .= $char;
            ++$pointer;
        }

        return $buffer;
    }

    public function peekUntilWhitespace(): string
    {
        return $this->peekUntilOneOf([' ', "\t"]);
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
        $this->incrementLineNumber(\strlen($subString) - \strlen(str_replace("\n", '', $subString)));

        return $subString;
    }

    public function consumeWhitespace(): void
    {
        $string = $this->peekUntilNotOneOf(self::WHITESPACE);
        $this->consume(\strlen($string));
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
