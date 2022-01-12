<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;

interface LexerInterface
{
    public function tokenize(string $input): TokenStreamInterface;
}
