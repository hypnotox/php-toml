<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Lexer;

use HypnoTox\Toml\Lexer\Lexer;
use HypnoTox\Toml\Lexer\LexerInterface;
use HypnoTox\Toml\Tests\Unit\BaseTest;

final class LexerTest extends BaseTest
{
    public function testTokenize(): void
    {
        $instance = new Lexer();

        $this->assertInstanceOf(Lexer::class, $instance);
        $this->assertInstanceOf(LexerInterface::class, $instance);
    }
}
