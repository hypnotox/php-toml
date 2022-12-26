<?php

namespace HypnoTox\Toml\Tests\Unit\Lexer\TokenLexer;

use HypnoTox\Toml\Lexer\TokenLexer\StringLexer;
use HypnoTox\Toml\Lexer\TokenLexer\TokenLexerInterface;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use HypnoTox\Toml\Token\TokenType;

abstract class AbstractTokenLexerTest extends BaseTest
{
    public function testGetTokenType(): void
    {
        $this->assertInstanceOf(TokenType::class, $this->getTokenLexer()->getTokenType());
    }

    abstract public function testCanTokenize(): void;

    abstract public function testTokenize(): void;

    abstract protected function getTokenLexer(): TokenLexerInterface;
}
