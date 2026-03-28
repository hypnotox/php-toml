<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser;

use HypnoTox\Toml\Parser\TableOrigin;
use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use HypnoTox\Toml\Tests\Unit\BaseTest;

final class TomlTableTest extends BaseTest
{
    public function testSetAndGet(): void
    {
        $table = new TomlTable();
        $value = new TomlValue(ValueType::String, 'hello');
        $table->set('key', $value);

        $this->assertSame($value, $table->get('key'));
    }

    public function testSetUpdatesExistingKey(): void
    {
        $table = new TomlTable();
        $v1 = new TomlValue(ValueType::String, 'first');
        $v2 = new TomlValue(ValueType::String, 'second');

        $table->set('key', $v1);
        $table->set('key', $v2);

        $this->assertSame($v2, $table->get('key'));
        $this->assertCount(1, $table->getEntries());
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $table = new TomlTable();
        $this->assertNull($table->get('missing'));
    }

    public function testHas(): void
    {
        $table = new TomlTable();
        $this->assertFalse($table->has('key'));

        $table->set('key', new TomlValue(ValueType::Integer, 42));
        $this->assertTrue($table->has('key'));
    }

    public function testIsEmpty(): void
    {
        $table = new TomlTable();
        $this->assertTrue($table->isEmpty());

        $table->set('key', new TomlValue(ValueType::Bool, true));
        $this->assertFalse($table->isEmpty());
    }

    public function testCount(): void
    {
        $table = new TomlTable();
        $this->assertCount(0, $table);

        $table->set('a', new TomlValue(ValueType::Integer, 1));
        $table->set('b', new TomlValue(ValueType::Integer, 2));
        $this->assertCount(2, $table);
    }

    public function testKeys(): void
    {
        $table = new TomlTable();
        $table->set('alpha', new TomlValue(ValueType::String, 'a'));
        $table->set('beta', new TomlValue(ValueType::String, 'b'));

        $this->assertSame(['alpha', 'beta'], $table->keys());
    }

    public function testDeepCloneWithNestedStructures(): void
    {
        $inner = new TomlTable();
        $inner->set('x', new TomlValue(ValueType::Integer, 1));

        $array = new TomlArray([
            new TomlValue(ValueType::String, 'item1'),
            new TomlValue(ValueType::String, 'item2'),
        ]);

        $table = new TomlTable(TableOrigin::Explicit);
        $table->set('nested', $inner);
        $table->set('list', $array);
        $table->set('val', new TomlValue(ValueType::Bool, true));

        $clone = $table->deepClone();

        // Clone is a separate instance
        $this->assertNotSame($table, $clone);
        $this->assertEquals(TableOrigin::Explicit, $clone->origin);

        // Nested table is cloned
        $clonedInner = $clone->get('nested');
        $this->assertInstanceOf(TomlTable::class, $clonedInner);
        $this->assertNotSame($inner, $clonedInner);

        // Array is cloned
        $clonedArray = $clone->get('list');
        $this->assertInstanceOf(TomlArray::class, $clonedArray);
        $this->assertNotSame($array, $clonedArray);

        // Values are preserved
        $this->assertSame(['nested', 'list', 'val'], $clone->keys());
    }

    public function testJsonSerializeEmpty(): void
    {
        $table = new TomlTable();
        $json = json_encode($table, \JSON_THROW_ON_ERROR);

        $this->assertSame('{}', $json);
    }

    public function testJsonSerializeWithEntries(): void
    {
        $table = new TomlTable();
        $table->set('name', new TomlValue(ValueType::String, 'test'));
        $table->set('count', new TomlValue(ValueType::Integer, 42));

        $json = json_encode($table, \JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);

        $this->assertIsObject($decoded);
        $this->assertObjectHasProperty('name', $decoded);
        $this->assertObjectHasProperty('count', $decoded);
    }

    public function testToAssocArray(): void
    {
        $inner = new TomlTable();
        $inner->set('nested', new TomlValue(ValueType::String, 'deep'));

        $array = new TomlArray([
            new TomlValue(ValueType::Integer, 1),
            new TomlValue(ValueType::Integer, 2),
        ]);

        $table = new TomlTable();
        $table->set('str', new TomlValue(ValueType::String, 'hello'));
        $table->set('sub', $inner);
        $table->set('arr', $array);

        $result = $table->toAssocArray();
        $this->assertSame([
            'str' => 'hello',
            'sub' => ['nested' => 'deep'],
            'arr' => [1, 2],
        ], $result);
    }

    public function testFromAssoc(): void
    {
        $value = new TomlValue(ValueType::String, 'test');
        $table = TomlTable::fromAssoc(['key' => $value], TableOrigin::Inline);

        $this->assertSame($value, $table->get('key'));
        $this->assertSame(TableOrigin::Inline, $table->origin);
    }

    public function testGetEntries(): void
    {
        $table = new TomlTable();
        $v1 = new TomlValue(ValueType::String, 'a');
        $v2 = new TomlValue(ValueType::Integer, 1);
        $table->set('first', $v1);
        $table->set('second', $v2);

        $entries = $table->getEntries();
        $this->assertSame([['first', $v1], ['second', $v2]], $entries);
    }
}
