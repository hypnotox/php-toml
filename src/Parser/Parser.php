<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Builder\BuilderInterface;
use HypnoTox\Toml\TomlInterface;

class Parser implements ParserInterface
{
    public function __construct(
        private LexerInterface $lexer,
        private BuilderInterface $builder,
    ) {
    }

    public function parse(string $input): TomlInterface
    {
        $this->lexer->tokenize($input);

        return $this->builder->build();
    }
}
