<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser\Lexer;

use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Parser\Lexer\Lexer;
use HypnoTox\Toml\Parser\Lexer\LexerInterface;
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

    public function testTokenizeThrowsIfUnableToTokenize(): void
    {
        $instance = new Lexer();

        $this->expectException(UnableToParseInputException::class);
        $instance->tokenize(str_repeat('test = # FOO', 101));
    }

    public function testTokenizeThrowsCorrectMessage(): void
    {
        $instance = new Lexer();

        try {
            $instance->tokenize(str_repeat('a', 101));
        } catch (UnableToParseInputException $exception) {
            $this->assertSame(
                'Unexpected T_EOF, expected one of: T_KEY, T_QUOTED_KEY, T_WHITESPACE, T_DOT, T_EQUALS',
                $exception->getMessage(),
            );
        }
    }

    public function tomlProvider(): array
    {
        return (new LexerProvider())->provide();
    }
}
