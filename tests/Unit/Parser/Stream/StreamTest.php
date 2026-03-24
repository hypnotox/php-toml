<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
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

    public function testPeekConsumeAndEndOfFile(): void
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

    public function testPeekMatchingAndConsumeMatching(): void
    {
        $instance = new StringStream("foo \t\n\r\n");

        $this->assertSame('foo', $instance->peekMatching('[a-z]+'));
        $this->assertSame('foo', $instance->consumeMatching('[a-z]+'));
        $this->assertSame('', $instance->peekMatching('[a-z]+'));
        $this->assertSame(" \t", $instance->consumeMatching('[ \t]+'));
        $this->assertSame("\n", $instance->consumeMatching('\R'));
        $this->assertSame("\r\n", $instance->consumeMatching('\R'));
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

    public function testLineAndColumnTracking(): void
    {
        $instance = new StringStream("ab\ncd\nef");

        $this->assertSame(1, $instance->getLine());
        $this->assertSame(1, $instance->getColumn());

        $instance->consume(2); // 'ab'
        $this->assertSame(1, $instance->getLine());
        $this->assertSame(3, $instance->getColumn());

        $instance->consume(1); // '\n'
        $this->assertSame(2, $instance->getLine());
        $this->assertSame(1, $instance->getColumn());

        $instance->consume(3); // 'cd\n'
        $this->assertSame(3, $instance->getLine());
        $this->assertSame(1, $instance->getColumn());

        $instance->consume(2); // 'ef'
        $this->assertSame(3, $instance->getLine());
        $this->assertSame(3, $instance->getColumn());
    }

    public function testSaveAndRestore(): void
    {
        $instance = new StringStream('abcdef');

        $instance->consume(3); // 'abc'
        $saved = $instance->save();

        $this->assertSame('d', $instance->peek());
        $instance->consume(2); // 'de'
        $this->assertSame('f', $instance->peek());

        $instance->restore($saved);
        $this->assertSame('d', $instance->peek());
        $this->assertSame(1, $instance->getLine());
        $this->assertSame(4, $instance->getColumn());
    }

    public function testSaveAndRestoreWithNewlines(): void
    {
        $instance = new StringStream("ab\ncd\nef");

        $instance->consume(3); // "ab\n"
        $saved = $instance->save();
        $this->assertSame(2, $instance->getLine());

        $instance->consume(3); // "cd\n"
        $this->assertSame(3, $instance->getLine());

        $instance->restore($saved);
        $this->assertSame(2, $instance->getLine());
        $this->assertSame(1, $instance->getColumn());
    }
}
