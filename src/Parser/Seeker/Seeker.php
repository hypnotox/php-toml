<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

final class Seeker implements SeekerInterface
{
    private int $pointer = 0;
    private int $inputLength;
    private int $lineNumber = 1;
    private int $lineOffset = 0;
    private int $lineWhitespaceLength = 0;

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
        $this->lineNumber += $n;
        $this->lineOffset = 0;
    }

    public function getLineOffset(): int
    {
        return $this->lineOffset;
    }

    public function getLineOffsetWithoutPrecedingWhitespace(): int
    {
        return $this->lineOffset - $this->lineWhitespaceLength;
    }

    public function getLineWhitespaceLength(): int
    {
        return $this->lineWhitespaceLength;
    }

    public function peek(int $n = 1): string
    {
        return substr($this->input, $this->pointer, $n);
    }

    public function peekUntilEOL(): string
    {
        $matches = $this->seek('~\n~');

        return $this->peek(\count($matches) > 0 ? $matches[0]['offset'] : ($this->inputLength - $this->pointer));
    }

    public function seek(string $pattern, string $flags = ''): array
    {
        $str = substr($this->input, $this->pointer);
        $matches = [];

        preg_match(
            $pattern,
            $str,
            $matches,
            \PREG_OFFSET_CAPTURE,
        );

//        dump($pattern, $str, $matches);

        return array_filter(
            array_map(
                static fn (array $match) => '' !== $match[0] ? [
                    'offset' => $match[1],
                    'length' => \strlen($match[0]),
                    'value'  => $match[0],
                ] : null,
                $matches,
            ),
        );
    }

    public function consume(int $n = 1): string
    {
        $subString = $this->peek($n);
        $this->forward($n);
        $matches = [];

        if (preg_match_all('~\n~', $subString, $matches) && \count($matches) > 0) {
            $this->incrementLineNumber(\count($matches[0]));
        }

        return $subString;
    }

    public function consumeUntilEOL(): string
    {
        $matches = $this->seek('~\n~');
        $string = $this->consume(\count($matches) > 0 ? $matches[0]['offset'] : ($this->inputLength - $this->pointer));
        $this->incrementLineNumber();
        $this->consumeWhitespace();

        return $string;
    }

    public function consumeWhitespace(): void
    {
        $whitespaceMatch = $this->seek('~^[\s]+?~');

        if (0 === \count($whitespaceMatch)) {
            return;
        }

        $this->lineWhitespaceLength = $whitespaceMatch[0]['length'];
        $string = $this->consume($this->lineWhitespaceLength);

        if (str_contains($string, "\n")) {
            $this->incrementLineNumber();
            $this->lineWhitespaceLength = 0;
            $this->consumeWhitespace();
        }
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
