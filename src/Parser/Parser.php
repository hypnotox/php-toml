<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Builder\BuilderInterface;
use HypnoTox\Toml\Parser\Exception\ParserException;
use HypnoTox\Toml\TomlInterface;

final class Parser implements ParserInterface
{
    public function __construct(
        private LexerInterface $lexer,
        private BuilderInterface $builder,
    ) {
    }

    /**
     * @throws ParserException
     */
    public function parse(string $input): TomlInterface
    {
        if (!mb_check_encoding($input, 'UTF-8')) {
            throw new ParserException('TOML must be UTF-8.');
        }

        $input = str_replace("\r\n", "\n", $input);
        $tokens = $this->lexer->tokenize($input);

        // TODO: Loop over $tokens and build TOML

        return $this->builder->build();
    }
}
