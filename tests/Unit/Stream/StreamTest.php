<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use HypnoTox\Toml\Token\TokenType;
use ReflectionMethod;

final class StreamTest extends BaseTest
{
    public function testConstruct(): void
    {
        $instance = new Stream('test');

        $this->assertInstanceOf(Stream::class, $instance);
        $this->assertInstanceOf(StreamInterface::class, $instance);

        $this->expectException(EncodingException::class);
        new Stream(utf8_decode("\x5A\x6F\xC3\xAB"));
    }

    public function testPeakConsumeAndEndOfFile(): void
    {
        $instance = new Stream("foo \t\n\r\n😀");

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
        $instance = new Stream("foo \t\n\r\n");

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
        $method = new ReflectionMethod(Stream::class, 'getSubstring');

        $this->assertSame($string, $method->invoke(new Stream($string)));
        $this->assertSame('abcd', $method->invoke(new Stream($string), 4));
        $this->assertSame("\t\n\r\n😀", $method->invoke(new Stream($string, 5)));
        $this->assertSame("\t\n\r\n", $method->invoke(new Stream($string, 5), 4));
        $this->assertSame('a', $method->invoke(new Stream($string), 1));
        $this->assertSame('b', $method->invoke(new Stream($string), 1, 1));
        $this->assertSame("\t", $method->invoke(new Stream($string, 5), 1));
    }
}
