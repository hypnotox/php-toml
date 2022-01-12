<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;

interface TokenizerInterface
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool;
}
