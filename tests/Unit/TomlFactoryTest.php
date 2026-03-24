<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit;

use HypnoTox\Toml\TomlFactory;
use HypnoTox\Toml\TomlFactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class TomlFactoryTest extends BaseTest
{
    #[DataProvider('tomlFactoryProvider')]
    public function testCanMake(TomlFactoryInterface $factory): void
    {
        // TODO: Add factory tests
        $this->expectNotToPerformAssertions();
    }

    public static function tomlFactoryProvider(): array
    {
        $factory = new TomlFactory();

        return [
            [$factory],
        ];
    }
}
