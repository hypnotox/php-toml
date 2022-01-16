<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer\Stream;

use function count;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenType;

final class TokenStream implements TokenStreamInterface
{
    private int $pointer = 0;

    /**
     * @param TokenInterface[] $tokens
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

    public function peek(int $n = 1): TokenInterface
    {
        return $this->tokens[$this->pointer + $n];
    }

    public function consume(): TokenInterface
    {
        ++$this->pointer;

        return $this->tokens[$this->pointer - 1];
    }

    public function consumeNewlines(): void
    {
        while (TokenType::T_RETURN === $this->peek()->getType()) {
            $this->consume();
        }
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
