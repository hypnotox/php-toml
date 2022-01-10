<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

final class TokenStream implements TokenStreamInterface
{
    /**
     * @param list<TokenInterface> $tokens
     */
    public function __construct(
        private array $tokens = [],
    ) {
    }

    public function addToken(TokenInterface $token): void
    {
        $this->tokens[] = $token;
    }

    public function current(): TokenInterface|false
    {
        return current($this->tokens);
    }

    public function next(): void
    {
        next($this->tokens);
    }

    public function key(): int
    {
        return key($this->tokens);
    }

    public function valid(): bool
    {
        return (bool) $this->current();
    }

    public function rewind(): void
    {
        reset($this->tokens);
    }
}
