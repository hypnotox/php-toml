<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Stream\StringStreamInterface;

interface TokenizerInterface
{
    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool;
}
