<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Parser\Token\TokenType;

/**
 * @internal
 */
final class StringStream implements StringStreamInterface
{
    private readonly int $length;

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

    public function getPointer(): int
    {
        return $this->pointer;
    }

    public function peek(int $length = 1): string
    {
        return $this->getSubstring($length);
    }

    public function peekMatching(string|TokenType $regex): string
    {
        $regex = $regex instanceof TokenType ? $regex->getRegex() : $regex;
        $matches = [];

        preg_match("~^$regex~u", $this->getSubstring(), $matches);

        if ([] !== $matches) {
            return $matches[0];
        }

        return '';
    }

    public function consume(int $length = 1): string
    {
        $result = $this->peek($length);
        $resultLength = mb_strlen($result, $this->encoding);
        $this->pointer += $resultLength;

        return $result;
    }

    public function consumeMatching(string|TokenType $regex): string
    {
        $regex = $regex instanceof TokenType ? $regex->getRegex() : $regex;
        $matches = [];

        preg_match("~^$regex~u", $this->getSubstring(), $matches);

        if ([] !== $matches) {
            $matched = $matches[0];
            $this->consume(mb_strlen($matched));

            return $matched;
        }

        return '';
    }

    public function matches(string|TokenType $regex): bool
    {
        $regex = $regex instanceof TokenType ? $regex->getRegex() : $regex;

        return 1 === preg_match("~^$regex~u", $this->getSubstring());
    }

    public function isEndOfFile(): bool
    {
        return $this->pointer === $this->length;
    }

    private function getSubstring(int $length = null, int $offset = 0): string
    {
        return mb_substr($this->input, $this->pointer + $offset, $length, $this->encoding);
    }
}
