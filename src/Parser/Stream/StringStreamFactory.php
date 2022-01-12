<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

final class StringStreamFactory implements StringStreamFactoryInterface
{
    public function make(string $input, int $lineNumber = 1, int $lineOffset = 0): StringStreamInterface
    {
        return new StringStream($input, $lineNumber, $lineOffset);
    }
}
