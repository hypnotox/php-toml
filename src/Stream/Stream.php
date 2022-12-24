<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

use HypnoTox\Toml\Exception\EncodingException;

/**
 * @internal
 */
final class Stream implements StreamInterface
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

    public function peek(int $length = 1): string
    {
        return $this->getSubstring($length);
    }

    public function seekUntil(array $characters): int
    {
        $maxOffset = $this->length - $this->pointer;
        $offset = null;
        $string = $this->getSubstring();

        foreach ($characters as $seekUnit) {
            $position = mb_strpos($string, $seekUnit);

            if (false !== $position) {
                $offset = min($maxOffset, $position);
            }
        }

        return $offset ?? $maxOffset;
    }

    public function seekUntilNot(array $characters): int
    {
        $buckets = [];

        foreach ($characters as $character) {
            $length = mb_strlen($character);

            if (!array_key_exists($length, $buckets)) {
                $buckets[$length] = [];
            }

            $buckets[$length][] = $character;
        }

        $maxOffset = $this->length - $this->pointer;

        for ($offset = 0; $offset <= $maxOffset; $offset++) {
            foreach ($buckets as $length => $bucket) {
                if (in_array($this->getSubstring($length, $offset), $bucket, true)) {
                    continue 2;
                }
            }

            return $offset;
        }

        return $maxOffset;
    }

    public function consume(int $length = 1): string
    {
        $result = $this->peek($length);
        $resultLength = mb_strlen($result, $this->encoding);
        $this->pointer += $resultLength;

        return $result;
    }

    public function consumeUntil(array $characters): string
    {
        return $this->consume($this->seekUntil($characters));
    }

    public function consumeUntilNot(array $characters): string
    {
        return $this->consume($this->seekUntilNot($characters));
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
