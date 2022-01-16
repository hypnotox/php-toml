<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer\Stream;

interface TokenStreamFactoryInterface
{
    public function make(): TokenStreamInterface;
}
