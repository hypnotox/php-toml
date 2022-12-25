<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Token\TokenInterface;

/**
 * @internal
 */
interface LexerInterface
{
    /**
     * @return TokenInterface[]
     */
    public function tokenize(string|Stream $input): array;
}
