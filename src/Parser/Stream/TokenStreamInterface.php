<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

use HypnoTox\Toml\Parser\Token\TokenInterface;

interface TokenStreamInterface extends StreamInterface, \Stringable
{
    /**
     * @param list<TokenInterface> $tokens
     */
    public function __construct(array $tokens);

    public function peek(): TokenInterface;

    public function consume(): TokenInterface;

    public function addToken(TokenInterface $token): void;
}
