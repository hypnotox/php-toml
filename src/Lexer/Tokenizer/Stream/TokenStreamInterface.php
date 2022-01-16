<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer\Stream;

use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenInterface;
use HypnoTox\Toml\Stream\StreamInterface;

interface TokenStreamInterface extends StreamInterface, \Stringable
{
    /**
     * @param \HypnoTox\Toml\Lexer\Tokenizer\Token\TokenInterface[] $tokens
     */
    public function __construct(array $tokens);

    public function peek(int $n = 1): TokenInterface;

    public function consume(): TokenInterface;

    public function consumeNewlines(): void;

    public function addToken(TokenInterface $token): void;
}
