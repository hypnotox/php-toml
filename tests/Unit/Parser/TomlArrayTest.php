<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser;

use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Tests\Unit\BaseTest;

final class TomlArrayTest extends BaseTest
{
    public function testConstructsWithItems(): void
    {
        $array = new TomlArray([1, 2, 3]);
        $this->assertSame([1, 2, 3], $array->items);
    }

    public function testConstructsEmpty(): void
    {
        $array = new TomlArray();
        $this->assertSame([], $array->items);
    }
}
