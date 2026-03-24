<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

/**
 * Marker class to distinguish TOML arrays from TOML tables in the internal data structure.
 * PHP coerces numeric string keys to integers, which makes array_is_list unreliable.
 *
 * @psalm-api
 */
final class TomlArray implements TomlNode
{
    /** @var list<TomlNode> */
    public array $items;

    /**
     * @param list<TomlNode> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}
