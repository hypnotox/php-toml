<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Stream;

final class TokenStreamFactory implements TokenStreamFactoryInterface
{
    public function make(): TokenStreamInterface
    {
        return new TokenStream();
    }
}
