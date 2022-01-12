<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

interface StringStreamFactoryInterface
{
    public function make(string $input, int $lineNumber = 1, int $lineOffset = 0): StringStreamInterface;
}
