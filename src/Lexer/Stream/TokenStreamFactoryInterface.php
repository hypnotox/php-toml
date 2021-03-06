<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Stream;

interface TokenStreamFactoryInterface
{
    public function make(): TokenStreamInterface;
}
