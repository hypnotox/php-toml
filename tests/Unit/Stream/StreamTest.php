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

    public function testSeekUntil(): void
    {
        $instance = new Stream("abcd \t\n\r\n😀");

        $this->assertSame(4, $instance->seekUntil([' ']));
        $this->assertSame(5, $instance->seekUntil(["\t"]));
        $this->assertSame(0, $instance->seekUntil(['a']));
        $this->assertSame(9, $instance->seekUntil(['😀']));
        $this->assertSame(6, $instance->seekUntil(TokenType::T_NEWLINE));
        $this->assertSame(10, $instance->seekUntil(['0']));
    }

    public function testSeekUntilNot(): void
    {
        $instance = new Stream("abcd \t\n\r\n😀");

        $this->assertSame(1, $instance->seekUntilNot(['a']));
        $this->assertSame(4, $instance->seekUntilNot(['a', 'b', 'c', 'd']));
        $this->assertSame(5, $instance->seekUntilNot(['a', 'b', 'c', 'd', ' ']));
        $this->assertSame(5, $instance->seekUntilNot(mb_str_split('abcd ')));
        $instance->consumeUntilNot(mb_str_split("abcd \t"));
        $this->assertSame(3, $instance->seekUntilNot(TokenType::T_NEWLINE));
        $this->assertSame(0, (new Stream(''))->seekUntilNot([' ']));
    }

    public function testConsumeUntil(): void
    {
        $instance = new Stream("abcd \t\n\r\n😀");

        $this->assertSame('abcd', $instance->consumeUntil([' ']));
        $this->assertSame(' ', $instance->consumeUntil(["\t"]));
        $this->assertSame("\t\n", $instance->consumeUntil(["\r\n"]));
        $this->assertSame("\r\n", $instance->consumeUntil(['😀']));
        $this->assertSame('😀', $instance->consumeUntil(TokenType::T_EOF));
    }

    public function testConsumeUntilNot(): void
    {
        $instance = new Stream("abcd \t\n\r\n😀");

        $this->assertSame('abcd', $instance->consumeUntilNot(mb_str_split('dcba')));
        $this->assertSame(" \t", $instance->consumeUntilNot([' ', "\t"]));
        $this->assertSame("\n\r\n", $instance->consumeUntilNot(["\n", "\r\n"]));
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
