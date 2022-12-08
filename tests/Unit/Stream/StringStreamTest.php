<?php

declare(strict_types=1);

namespace Tests\Stream;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Stream\StreamInterface;
use HypnoTox\Toml\Stream\StringStream;
use Tests\BaseTest;

final class StringStreamTest extends BaseTest
{
    public function testConstruct(): void
    {
        $instance = new StringStream('test');

        $this->assertInstanceOf(StringStream::class, $instance);
        $this->assertInstanceOf(StreamInterface::class, $instance);

        $this->expectException(EncodingException::class);
        new StringStream(utf8_decode("\x5A\x6F\xC3\xAB"));
    }

    public function testPeakAndConsume(): void
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
    }

    public function testSeekUntil(): void
    {
        $instance = new StringStream("asdf \t\n\r\n😀");

        $this->assertSame(4, $instance->seekUntil([' ']));
        $this->assertSame(5, $instance->seekUntil(["\t"]));
        $this->assertSame(0, $instance->seekUntil(['a']));
        $this->assertSame(9, $instance->seekUntil(['😀']));
    }
}
