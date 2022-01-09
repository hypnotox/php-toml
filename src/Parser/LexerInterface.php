<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

interface LexerInterface
{
    public function tokenize(string $input): TokenStreamInterface;
}
