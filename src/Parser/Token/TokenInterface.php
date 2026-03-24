<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

/**
 * @internal
 *
 * @psalm-api
 */
interface TokenInterface
{
    public function getType(): TokenType;

    public function getValue(): mixed;
}
