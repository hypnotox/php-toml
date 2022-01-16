<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Expression;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\TokenParser\TokenParserInterface;

interface ExpressionParserInterface extends TokenParserInterface
{
    /**
     * @throws \HypnoTox\Toml\Exception\TomlExceptionInterface
     */
    public function parse(TomlBuilderInterface $builder, TokenStreamInterface $stream): void;
}
