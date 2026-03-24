<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit;

use HypnoTox\Toml\Toml;
use HypnoTox\Toml\TomlFactory;
use HypnoTox\Toml\TomlInterface;

final class TomlFactoryTest extends BaseTest
{
    public function testMakeCreatesEmptyToml(): void
    {
        $factory = new TomlFactory();
        $toml = $factory->make();

        $this->assertInstanceOf(TomlInterface::class, $toml);
        $this->assertInstanceOf(Toml::class, $toml);
        $this->assertSame([], $toml->toArray());
    }

    public function testFromStringParsesToml(): void
    {
        $factory = new TomlFactory();
        $toml = $factory->fromString("title = \"TOML Example\"\n");

        $this->assertInstanceOf(TomlInterface::class, $toml);
        $this->assertSame('TOML Example', $toml->get('title'));
    }

    public function testEndToEndFactoryParseGetValues(): void
    {
        $factory = new TomlFactory();
        $input = <<<'TOML'
            [server]
            host = "localhost"
            port = 8080
            enabled = true
            TOML;

        $toml = $factory->fromString($input);

        $this->assertSame('localhost', $toml->get('server.host'));
        $this->assertSame(8080, $toml->get('server.port'));
        $this->assertTrue($toml->get('server.enabled'));
    }
}
