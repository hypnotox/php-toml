<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

use HypnoTox\Toml\Parser\Token\Token;

/**
 * @internal
 */
interface LexerInterface
{
    public function next(LexerContext $context): Token;

    public function peek(LexerContext $context): Token;
}
