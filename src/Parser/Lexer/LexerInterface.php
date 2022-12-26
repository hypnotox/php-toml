<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Token\TokenInterface;

/**
 * @internal
 */
interface LexerInterface
{
    /**
     * @return TokenInterface[]
     */
    public function tokenize(string|StringStream $input): array;
}
