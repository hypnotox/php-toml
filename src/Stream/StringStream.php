<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Stream;

use HypnoTox\Toml\Exception\EncodingException;

/**
 * @template-implements StreamInterface<string, string>
 */
final class StringStream implements StreamInterface
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

    /**
     * {@inheritDoc}
     */
    public function peek(int $length = 1): string
    {
        return mb_substr($this->input, $this->pointer, $length, $this->encoding);
    }

    /**
     * {@inheritDoc}
     */
    public function consume(int $length = 1): string
    {
        $result = $this->peek($length);
        $resultLength = mb_strlen($result, $this->encoding);
        $this->pointer += $resultLength;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function seekUntil(array $seekUnits): int
    {
        $index = $this->length;
        $string = mb_substr($this->input, $this->pointer);

        foreach ($seekUnits as $seekUnit) {
            $position = mb_strpos($string, $seekUnit);

            if (false !== $position) {
                $index = min($index, $position);
            }
        }

        return $index;
    }
}
