<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit;

use HypnoTox\Toml\TomlFactory;
use HypnoTox\Toml\TomlFactoryInterface;

final class TomlFactoryTest extends BaseTest
{
    /**
     * @dataProvider tomlFactoryProvider
     */
    public function testCanMake(TomlFactoryInterface $factory): void
    {
        // TODO: Add factory tests
        $this->expectNotToPerformAssertions();
    }

    public function tomlFactoryProvider()
    {
        $factory = new TomlFactory();

        return [
            [$factory],
        ];
    }
}
