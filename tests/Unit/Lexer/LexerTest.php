<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Lexer;

use HypnoTox\Toml\Lexer\Lexer;
use HypnoTox\Toml\Lexer\LexerInterface;
use HypnoTox\Toml\Tests\Unit\BaseTest;

final class LexerTest extends BaseTest
{
    public function testConstruct(): void
    {
        $instance = new Lexer();

        $this->assertInstanceOf(Lexer::class, $instance);
        $this->assertInstanceOf(LexerInterface::class, $instance);
    }

    /**
     * @dataProvider tomlProvider
     */
    public function testTokenize(string $toml, array $expected): void
    {
        $instance = new Lexer();
        $result = $instance->tokenize($toml);

        $this->assertEquals($expected, $result);
    }

    public function tomlProvider(): array
    {
        return (new LexerProvider())->provide();
    }
}
