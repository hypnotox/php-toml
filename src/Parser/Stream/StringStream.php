<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use Override;

/**
 * @internal
 *
 * @psalm-api
 */
final class StringStream implements StringStreamInterface
{
    private readonly int $length;
    private int $line = 1;
    private int $column = 1;

    /**
     * @throws EncodingException
     */
    public function __construct(
        private readonly string $input,
        private int $pointer = 0,
        private readonly string $encoding = 'UTF-8',
    ) {
        if (!mb_check_encoding($this->input, $this->encoding)) {
            throw new EncodingException();
        }

        $this->length = mb_strlen($this->input, $this->encoding);
    }

    #[Override]
    public function getPointer(): int
    {
        return $this->pointer;
    }

    #[Override]
    public function getLine(): int
    {
        return $this->line;
    }

    #[Override]
    public function getColumn(): int
    {
        return $this->column;
    }

    #[Override]
    public function peek(int $length = 1): string
    {
        return $this->getSubstring($length);
    }

    #[Override]
    public function peekMatching(string $regex): string
    {
        $matches = [];

        preg_match("~^$regex~u", $this->getSubstring(), $matches);

        if ([] !== $matches) {
            return $matches[0];
        }

        return '';
    }

    #[Override]
    public function consume(int $length = 1): string
    {
        $result = $this->peek($length);
        $resultLength = mb_strlen($result, $this->encoding);
        $this->pointer += $resultLength;

        $this->updatePosition($result);

        return $result;
    }

    #[Override]
    public function consumeMatching(string $regex): string
    {
        $matches = [];

        preg_match("~^$regex~u", $this->getSubstring(), $matches);

        if ([] !== $matches) {
            $matched = $matches[0];
            $this->consume(mb_strlen($matched));

            return $matched;
        }

        return '';
    }

    #[Override]
    public function matches(string $regex): bool
    {
        return 1 === preg_match("~^$regex~u", $this->getSubstring());
    }

    #[Override]
    public function isEndOfFile(): bool
    {
        return $this->pointer === $this->length;
    }

    #[Override]
    public function save(): array
    {
        return [$this->pointer, $this->line, $this->column];
    }

    #[Override]
    public function restore(array $state): void
    {
        [$this->pointer, $this->line, $this->column] = $state;
    }

    private function getSubstring(?int $length = null, int $offset = 0): string
    {
        return mb_substr($this->input, $this->pointer + $offset, $length, $this->encoding);
    }

    private function updatePosition(string $consumed): void
    {
        $len = mb_strlen($consumed, $this->encoding);

        for ($i = 0; $i < $len; ++$i) {
            $char = mb_substr($consumed, $i, 1, $this->encoding);

            if ("\n" === $char) {
                ++$this->line;
                $this->column = 1;
            } else {
                ++$this->column;
            }
        }
    }
}
