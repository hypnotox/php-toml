<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Encoder;

use DateTimeImmutable;
use HypnoTox\Toml\Encoder\TomlEncoder;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use HypnoTox\Toml\Toml;

final class TomlEncoderTest extends BaseTest
{
    private TomlEncoder $encoder;
    private Parser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encoder = new TomlEncoder();
        $this->parser = new Parser();
    }

    public function testEncodeSimpleKeyValuePairs(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'title' => new TomlValue(ValueType::String, 'TOML Example'),
            'enabled' => new TomlValue(ValueType::Bool, true),
            'count' => new TomlValue(ValueType::Integer, 42),
            'pi' => new TomlValue(ValueType::Float, 3.14),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('title = "TOML Example"', $result);
        self::assertStringContainsString('enabled = true', $result);
        self::assertStringContainsString('count = 42', $result);
        self::assertStringContainsString('pi = 3.14', $result);
    }

    public function testEncodeNestedTables(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'database' => TomlTable::fromAssoc([
                'server' => new TomlValue(ValueType::String, '192.168.1.1'),
                'port' => new TomlValue(ValueType::Integer, 5432),
                'enabled' => new TomlValue(ValueType::Bool, true),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('[database]', $result);
        self::assertStringContainsString('server = "192.168.1.1"', $result);
        self::assertStringContainsString('port = 5432', $result);
        self::assertStringContainsString('enabled = true', $result);
    }

    public function testEncodeDeeplyNestedTables(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'servers' => TomlTable::fromAssoc([
                'alpha' => TomlTable::fromAssoc([
                    'ip' => new TomlValue(ValueType::String, '10.0.0.1'),
                ]),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('[servers.alpha]', $result);
        self::assertStringContainsString('ip = "10.0.0.1"', $result);
    }

    public function testEncodeArraysOfTables(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'products' => new TomlArray([
                TomlTable::fromAssoc([
                    'name' => new TomlValue(ValueType::String, 'Hammer'),
                    'sku' => new TomlValue(ValueType::Integer, 738594937),
                ]),
                TomlTable::fromAssoc([
                    'name' => new TomlValue(ValueType::String, 'Nail'),
                    'sku' => new TomlValue(ValueType::Integer, 284758393),
                ]),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('[[products]]', $result);
        self::assertStringContainsString('name = "Hammer"', $result);
        self::assertStringContainsString('name = "Nail"', $result);
        self::assertSame(2, substr_count($result, '[[products]]'));
    }

    public function testEncodeSimpleArray(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'colors' => new TomlArray([
                new TomlValue(ValueType::String, 'red'),
                new TomlValue(ValueType::String, 'green'),
                new TomlValue(ValueType::String, 'blue'),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('colors = ["red", "green", "blue"]', $result);
    }

    public function testEncodeEmptyArray(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'empty' => new TomlArray([]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('empty = []', $result);
    }

    public function testEncodeStringEscaping(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'backslash' => new TomlValue(ValueType::String, 'back\\slash'),
            'quote' => new TomlValue(ValueType::String, 'has "quotes"'),
            'newline' => new TomlValue(ValueType::String, "line1\nline2"),
            'tab' => new TomlValue(ValueType::String, "col1\tcol2"),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('backslash = "back\\\\slash"', $result);
        self::assertStringContainsString('quote = "has \\"quotes\\""', $result);
        self::assertStringContainsString('newline = "line1\\nline2"', $result);
        self::assertStringContainsString('tab = "col1\\tcol2"', $result);
    }

    public function testEncodeSpecialFloats(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'pos_inf' => new TomlValue(ValueType::Float, \INF),
            'neg_inf' => new TomlValue(ValueType::Float, -\INF),
            'nan_val' => new TomlValue(ValueType::Float, fdiv(0, 0)),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('pos_inf = inf', $result);
        self::assertStringContainsString('neg_inf = -inf', $result);
        self::assertStringContainsString('nan_val = nan', $result);
    }

    public function testEncodeBooleans(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'yes' => new TomlValue(ValueType::Bool, true),
            'no' => new TomlValue(ValueType::Bool, false),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('yes = true', $result);
        self::assertStringContainsString('no = false', $result);
    }

    public function testEncodeDatetimes(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'odt' => new TomlValue(ValueType::OffsetDateTime, '1979-05-27T07:32:00Z'),
            'ldt' => new TomlValue(ValueType::LocalDateTime, '1979-05-27T07:32:00'),
            'ld' => new TomlValue(ValueType::LocalDate, '1979-05-27'),
            'lt' => new TomlValue(ValueType::LocalTime, '07:32:00'),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('odt = 1979-05-27T07:32:00Z', $result);
        self::assertStringContainsString('ldt = 1979-05-27T07:32:00', $result);
        self::assertStringContainsString('ld = 1979-05-27', $result);
        self::assertStringContainsString('lt = 07:32:00', $result);
    }

    public function testEncodeQuotedKeys(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'simple' => new TomlValue(ValueType::String, 'bare key'),
            'with spaces' => new TomlValue(ValueType::String, 'quoted key'),
            'with.dot' => new TomlValue(ValueType::String, 'dotted key'),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('simple = "bare key"', $result);
        self::assertStringContainsString('"with spaces" = "quoted key"', $result);
        self::assertStringContainsString('"with.dot" = "dotted key"', $result);
    }

    public function testEncodeScalarsBeforeTables(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'title' => new TomlValue(ValueType::String, 'My Title'),
            'database' => TomlTable::fromAssoc([
                'host' => new TomlValue(ValueType::String, 'localhost'),
            ]),
            'version' => new TomlValue(ValueType::Integer, 1),
        ]));

        $result = $this->encoder->encode($toml);

        // scalars should come before tables
        $titlePos = strpos($result, 'title = ');
        $versionPos = strpos($result, 'version = ');
        $databasePos = strpos($result, '[database]');

        self::assertNotFalse($titlePos);
        self::assertNotFalse($versionPos);
        self::assertNotFalse($databasePos);

        // Both scalars should appear before the table header
        self::assertLessThan($databasePos, $titlePos);
        self::assertLessThan($databasePos, $versionPos);
    }

    public function testFromArrayWithString(): void
    {
        $toml = Toml::fromArray(['name' => 'test']);

        self::assertSame('test', $toml->get('name'));
    }

    public function testFromArrayWithInteger(): void
    {
        $toml = Toml::fromArray(['count' => 42]);

        self::assertSame(42, $toml->get('count'));
    }

    public function testFromArrayWithFloat(): void
    {
        $toml = Toml::fromArray(['ratio' => 3.14]);

        self::assertSame(3.14, $toml->get('ratio'));
    }

    public function testFromArrayWithBool(): void
    {
        $toml = Toml::fromArray(['enabled' => true]);

        self::assertTrue($toml->get('enabled'));
    }

    public function testFromArrayWithDatetime(): void
    {
        $dt = new DateTimeImmutable('1979-05-27T07:32:00+00:00');
        $toml = Toml::fromArray(['created' => $dt]);

        self::assertSame('1979-05-27T07:32:00+00:00', $toml->get('created'));
    }

    public function testFromArrayWithList(): void
    {
        $toml = Toml::fromArray(['colors' => ['red', 'green', 'blue']]);
        $data = $toml->getData();

        self::assertInstanceOf(TomlArray::class, $data->get('colors'));
    }

    public function testFromArrayWithNestedTable(): void
    {
        $toml = Toml::fromArray([
            'database' => [
                'host' => 'localhost',
                'port' => 5432,
            ],
        ]);

        self::assertSame('localhost', $toml->get('database.host'));
        self::assertSame(5432, $toml->get('database.port'));
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $toml = Toml::fromArray(['empty' => []]);
        $data = $toml->getData();

        self::assertInstanceOf(TomlArray::class, $data->get('empty'));
    }

    public function testRoundTripSimple(): void
    {
        $input = <<<'TOML'
            title = "TOML Example"
            enabled = true
            count = 42
            TOML;

        $parsed = $this->parser->parse($input);
        $array = $parsed->toArray();
        $rebuilt = Toml::fromArray($array);
        $encoded = $rebuilt->toString();
        $reparsed = $this->parser->parse($encoded);

        self::assertSame($parsed->toArray(), $reparsed->toArray());
    }

    public function testRoundTripNestedTables(): void
    {
        $input = <<<'TOML'
            [database]
            server = "192.168.1.1"
            port = 5432
            enabled = true
            TOML;

        $parsed = $this->parser->parse($input);
        $array = $parsed->toArray();
        $rebuilt = Toml::fromArray($array);
        $encoded = $rebuilt->toString();
        $reparsed = $this->parser->parse($encoded);

        self::assertSame($parsed->toArray(), $reparsed->toArray());
    }

    public function testRoundTripArrayOfTables(): void
    {
        $input = <<<'TOML'
            [[products]]
            name = "Hammer"
            sku = 738594937

            [[products]]
            name = "Nail"
            sku = 284758393
            TOML;

        $parsed = $this->parser->parse($input);
        $array = $parsed->toArray();
        $rebuilt = Toml::fromArray($array);
        $encoded = $rebuilt->toString();
        $reparsed = $this->parser->parse($encoded);

        self::assertSame($parsed->toArray(), $reparsed->toArray());
    }

    public function testRoundTripMixedTypes(): void
    {
        $input = <<<'TOML'
            title = "Test"
            version = 1
            pi = 3.14
            enabled = true
            colors = ["red", "green", "blue"]

            [owner]
            name = "Tom"
            TOML;

        $parsed = $this->parser->parse($input);
        $array = $parsed->toArray();
        $rebuilt = Toml::fromArray($array);
        $encoded = $rebuilt->toString();
        $reparsed = $this->parser->parse($encoded);

        self::assertSame($parsed->toArray(), $reparsed->toArray());
    }

    public function testToTomlStringMethod(): void
    {
        $toml = Toml::fromArray([
            'name' => 'test',
            'version' => 1,
        ]);

        $result = $toml->toString();

        self::assertStringContainsString('name = "test"', $result);
        self::assertStringContainsString('version = 1', $result);
    }

    public function testEncodeEmptyToml(): void
    {
        $toml = new Toml();
        $result = $this->encoder->encode($toml);

        self::assertSame('', $result);
    }

    public function testEncodeInlineTable(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'point' => new TomlArray([
                TomlTable::fromAssoc([
                    'x' => new TomlValue(ValueType::Integer, 1),
                    'y' => new TomlValue(ValueType::Integer, 2),
                ]),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        // Should use array-of-tables syntax for arrays of tables
        self::assertStringContainsString('[[point]]', $result);
    }

    public function testEncodeIntegerArray(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'ports' => new TomlArray([
                new TomlValue(ValueType::Integer, 8000),
                new TomlValue(ValueType::Integer, 8001),
                new TomlValue(ValueType::Integer, 8002),
            ]),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringContainsString('ports = [8000, 8001, 8002]', $result);
    }

    public function testEncodeEndsWithNewline(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'key' => new TomlValue(ValueType::String, 'value'),
        ]));

        $result = $this->encoder->encode($toml);

        self::assertStringEndsWith("\n", $result);
    }

    public function testBlankLineBetweenSections(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'title' => new TomlValue(ValueType::String, 'Test'),
            'a' => TomlTable::fromAssoc([
                'key' => new TomlValue(ValueType::String, 'val'),
            ]),
            'b' => TomlTable::fromAssoc([
                'key' => new TomlValue(ValueType::String, 'val'),
            ]),
        ]));

        $result = $this->encoder->encode($toml);
        $lines = explode("\n", $result);

        // Find the blank line before [b] section
        $foundBlank = false;
        for ($i = 1; $i < \count($lines); ++$i) {
            if ('[b]' === $lines[$i] && '' === $lines[$i - 1]) {
                $foundBlank = true;
            }
        }

        self::assertTrue($foundBlank, 'Expected a blank line before [b] section');
    }

    public function testFromArrayRoundTripPreservesTypes(): void
    {
        $input = [
            'string' => 'hello',
            'integer' => 42,
            'float' => 3.14,
            'bool' => true,
            'list' => [1, 2, 3],
            'nested' => [
                'key' => 'value',
            ],
        ];

        $toml = Toml::fromArray($input);
        $output = $toml->toArray();

        self::assertSame('hello', $output['string']);
        self::assertSame(42, $output['integer']);
        self::assertSame(3.14, $output['float']);
        self::assertTrue($output['bool']);
        self::assertSame([1, 2, 3], $output['list']);
        self::assertSame(['key' => 'value'], $output['nested']);
    }
}
