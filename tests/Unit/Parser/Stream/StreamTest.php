<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use ReflectionMethod;

final class StreamTest extends BaseTest
{
    public function testConstruct(): void
    {
        $instance = new StringStream('test');

        $this->assertInstanceOf(StringStream::class, $instance);
        $this->assertInstanceOf(StringStreamInterface::class, $instance);

        $this->expectException(EncodingException::class);
        new StringStream(mb_convert_encoding("\x5A\x6F\xC3\xAB", 'ISO-8859-1', 'UTF-8'));
    }

    public function testPeakConsumeAndEndOfFile(): void
    {
        $instance = new StringStream("foo \t\n\r\n😀");

        $this->assertSame('f', $instance->peek());
        $this->assertSame('foo', $instance->peek(3));
        $this->assertSame('foo ', $instance->peek(4));
        $this->assertSame('foo ', $instance->consume(4));
        $this->assertSame("\t\n\r\n", $instance->peek(4));
        $this->assertSame("\t\n\r\n", $instance->consume(4));
        $this->assertSame('😀', $instance->peek());
        $this->assertSame('😀', $instance->consume());
        $this->assertTrue($instance->isEndOfFile());
    }

    public function testPeakMatchingAndConsumeMatching(): void
    {
        $instance = new StringStream("foo \t\n\r\n");

        $this->assertSame('foo', $instance->peekMatching(TokenType::T_BASIC_STRING));
        $this->assertSame('foo', $instance->consumeMatching(TokenType::T_BASIC_STRING));
        $this->assertSame('', $instance->peekMatching(TokenType::T_BASIC_STRING));
        $this->assertSame(" \t", $instance->consumeMatching("( \t)+"));
        $this->assertSame("\n", $instance->consumeMatching(TokenType::T_NEWLINE));
        $this->assertSame("\r\n", $instance->consumeMatching(TokenType::T_NEWLINE));
        $this->assertTrue($instance->isEndOfFile());
    }

    public function testGetSubstring(): void
    {
        $string = "abcd \t\n\r\n😀";
        $method = new ReflectionMethod(StringStream::class, 'getSubstring');

        $this->assertSame($string, $method->invoke(new StringStream($string)));
        $this->assertSame('abcd', $method->invoke(new StringStream($string), 4));
        $this->assertSame("\t\n\r\n😀", $method->invoke(new StringStream($string, 5)));
        $this->assertSame("\t\n\r\n", $method->invoke(new StringStream($string, 5), 4));
        $this->assertSame('a', $method->invoke(new StringStream($string), 1));
        $this->assertSame('b', $method->invoke(new StringStream($string), 1, 1));
        $this->assertSame("\t", $method->invoke(new StringStream($string, 5), 1));
    }
}
