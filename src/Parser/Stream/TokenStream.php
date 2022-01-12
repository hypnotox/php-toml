<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

use HypnoTox\Toml\Parser\Token\TokenInterface;

final class TokenStream implements TokenStreamInterface
{
    private int $pointer = 0;

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

    public function getPointer(): int
    {
        return $this->pointer;
    }

    public function peek(): TokenInterface
    {
        return $this->tokens[$this->pointer];
    }

    public function consume(): TokenInterface
    {
        ++$this->pointer;

        return $this->tokens[$this->pointer - 1];
    }

    public function isEOF(): bool
    {
        return $this->pointer >= count($this->tokens);
    }

    public function __toString()
    {
        return implode(
            "\n",
            array_map(
                static fn (TokenInterface $token) => (string) $token,
                $this->tokens,
            ),
        );
    }
}
