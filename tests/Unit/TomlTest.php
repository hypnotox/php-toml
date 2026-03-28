<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit;

use DateTimeImmutable;
use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use HypnoTox\Toml\Toml;
use Stringable;

final class TomlTest extends BaseTest
{
    public function testGetSimpleKey(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'key' => new TomlValue(ValueType::String, 'value'),
        ]));

        $this->assertSame('value', $toml->get('key'));
    }

    public function testGetDottedKey(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'a' => TomlTable::fromAssoc([
                'b' => new TomlValue(ValueType::Integer, 42),
            ]),
        ]));

        $this->assertSame(42, $toml->get('a.b'));
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $toml = new Toml();

        $this->assertNull($toml->get('missing'));
    }

    public function testGetReturnsNullForMissingNestedKey(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'a' => new TomlValue(ValueType::String, 'value'),
        ]));

        $this->assertNull($toml->get('a.b'));
    }

    public function testSetReturnsNewImmutableInstance(): void
    {
        $original = new Toml();
        $modified = $original->set('key', new TomlValue(ValueType::String, 'value'));

        $this->assertNotSame($original, $modified);
        $this->assertNull($original->get('key'));
        $this->assertSame('value', $modified->get('key'));
    }

    public function testSetNestedKey(): void
    {
        $toml = new Toml();
        $modified = $toml->set('a.b.c', new TomlValue(ValueType::Integer, 123));

        $this->assertSame(123, $modified->get('a.b.c'));
    }

    public function testToArrayUnwrapsTomlValues(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'string' => new TomlValue(ValueType::String, 'hello'),
            'number' => new TomlValue(ValueType::Integer, 42),
            'nested' => TomlTable::fromAssoc([
                'bool' => new TomlValue(ValueType::Bool, true),
            ]),
        ]));

        $array = $toml->toArray();

        $this->assertSame('hello', $array['string']);
        $this->assertSame(42, $array['number']);
        $this->assertIsArray($array['nested']);
        $this->assertTrue($array['nested']['bool']);
    }

    public function testToArrayReturnsEmptyArrayForEmptyToml(): void
    {
        $toml = new Toml();

        $this->assertSame([], $toml->toArray());
    }

    public function testToJsonProducesTomlTestFormat(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'title' => new TomlValue(ValueType::String, 'TOML Example'),
        ]));

        $json = $toml->toJson();
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('title', $decoded);
        $this->assertSame('string', $decoded['title']['type']);
        $this->assertSame('TOML Example', $decoded['title']['value']);
    }

    public function testToJsonWithMultipleTypes(): void
    {
        $toml = new Toml(TomlTable::fromAssoc([
            'name' => new TomlValue(ValueType::String, 'test'),
            'count' => new TomlValue(ValueType::Integer, 5),
            'pi' => new TomlValue(ValueType::Float, 3.14),
            'enabled' => new TomlValue(ValueType::Bool, true),
        ]));

        $json = $toml->toJson();
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        $this->assertSame('string', $decoded['name']['type']);
        $this->assertSame('test', $decoded['name']['value']);
        $this->assertSame('integer', $decoded['count']['type']);
        $this->assertSame('5', $decoded['count']['value']);
        $this->assertSame('float', $decoded['pi']['type']);
        $this->assertSame('bool', $decoded['enabled']['type']);
        $this->assertSame('true', $decoded['enabled']['value']);
    }

    public function testFromArrayPreservesDateTimeMicroseconds(): void
    {
        $dt = new DateTimeImmutable('1979-05-27T07:32:00.123456+00:00');
        $toml = Toml::fromArray(['ts' => $dt]);
        $output = $toml->toString();
        $this->assertStringContainsString('1979-05-27T07:32:00.123456+00:00', $output);
    }

    public function testFromArrayDateTimeOmitsZeroMicroseconds(): void
    {
        $dt = new DateTimeImmutable('1979-05-27T07:32:00+00:00');
        $toml = Toml::fromArray(['ts' => $dt]);
        $output = $toml->toString();
        $this->assertStringContainsString('1979-05-27T07:32:00+00:00', $output);
        $this->assertStringNotContainsString('.000000', $output);
    }

    public function testGetDataReturnsRawData(): void
    {
        $value = new TomlValue(ValueType::String, 'hello');
        $toml = new Toml(TomlTable::fromAssoc(['key' => $value]));

        $data = $toml->getData();

        $this->assertSame($value, $data->get('key'));
    }

    public function testGetReturnsNonValueNode(): void
    {
        // get() on a key that maps to a TomlTable should return the TomlTable, not unwrap it
        $inner = new TomlTable();
        $inner->set('child', new TomlValue(ValueType::String, 'nested'));

        $root = new TomlTable();
        $root->set('sub', $inner);

        $toml = new Toml($root);
        $result = $toml->get('sub');
        $this->assertInstanceOf(TomlTable::class, $result);
    }

    public function testGetReturnsArrayNode(): void
    {
        $array = new TomlArray([
            new TomlValue(ValueType::Integer, 1),
            new TomlValue(ValueType::Integer, 2),
        ]);

        $root = new TomlTable();
        $root->set('list', $array);

        $toml = new Toml($root);
        $result = $toml->get('list');
        $this->assertInstanceOf(TomlArray::class, $result);
    }

    public function testFromArrayWithObjectFallsBackToString(): void
    {
        // An object that isn't DateTimeInterface should be cast to string
        $obj = new class implements Stringable {
            public function __toString(): string
            {
                return 'stringified';
            }
        };

        $toml = Toml::fromArray(['obj' => $obj]);
        $this->assertSame('stringified', $toml->get('obj'));
    }

    public function testToJsonWithExactIntegerFloat(): void
    {
        // Float value that is an exact integer (e.g. 42.0) — tests formatFloatForTest
        $root = new TomlTable();
        $root->set('val', new TomlValue(ValueType::Float, 42.0));

        $toml = new Toml($root);
        $json = $toml->toJson();

        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        $this->assertSame('float', $decoded['val']['type']);
        $this->assertSame('42', $decoded['val']['value']);
    }

    public function testToJsonWithLargeExactFloat(): void
    {
        // Float that is exact integer but too large for int cast round-trip
        $root = new TomlTable();
        $root->set('val', new TomlValue(ValueType::Float, 1e19));

        $toml = new Toml($root);
        $json = $toml->toJson();

        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        $this->assertSame('float', $decoded['val']['type']);
    }
}
