<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Expression;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Parser\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\TokenParser\TokenParserInterface;

interface ExpressionParserInterface extends TokenParserInterface
{
    /**
     * @throws TomlExceptionInterface
     */
    public function parse(TomlBuilderInterface $builder, TokenStreamInterface $stream): void;
}