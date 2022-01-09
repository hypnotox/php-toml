<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

class Lexer implements LexerInterface
{
    public function tokenize(string $input): TokenStreamInterface
    {
        // convert input to array and filter empty lines
        $lines = array_filter(explode("\n", $input));

        dump($lines);

        return new TokenStream();
    }
}
