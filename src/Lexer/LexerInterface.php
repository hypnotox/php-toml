<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;

interface LexerInterface
{
    public function tokenize(string $input): TokenStreamInterface;
}
