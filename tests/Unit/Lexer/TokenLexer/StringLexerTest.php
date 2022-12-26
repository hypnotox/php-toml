<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Lexer\TokenLexer;

use HypnoTox\Toml\Lexer\TokenLexer\StringLexer;
use HypnoTox\Toml\Lexer\TokenLexer\TokenLexerInterface;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Token\Token;
use HypnoTox\Toml\Token\TokenType;

final class StringLexerTest extends AbstractTokenLexerTest
{
    public function testCanTokenize(): void
    {
        $tokenLexer = $this->getTokenLexer();

        $this->assertTrue($tokenLexer->canTokenize(new Stream('abcd')));
        $this->assertFalse($tokenLexer->canTokenize(new Stream('1')));
        $this->assertFalse($tokenLexer->canTokenize(new Stream('')));
    }

    public function testTokenize(): void
    {
        $tokenLexer = $this->getTokenLexer();

        $this->assertEquals(
            [
                new Token(
                    TokenType::T_STRING,
                    'abcd',
                ),
            ],
            $tokenLexer->tokenize('abcd'),
        );
    }

    protected function getTokenLexer(): TokenLexerInterface
    {
        return new StringLexer();
    }
}
