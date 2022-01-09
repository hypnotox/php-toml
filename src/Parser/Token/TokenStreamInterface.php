<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

interface TokenStreamInterface extends \Iterator
{
    /**
     * @param list<TokenInterface> $tokens
     */
    public function __construct(array $tokens);

    public function addToken(TokenInterface $token): void;
}
