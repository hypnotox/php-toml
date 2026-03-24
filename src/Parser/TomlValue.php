<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

/**
 * @psalm-api
 */
final readonly class TomlValue implements TomlNode
{
    public function __construct(
        public ValueType $type,
        public mixed $value,
    ) {
    }
}
