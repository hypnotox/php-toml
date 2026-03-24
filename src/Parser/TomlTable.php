<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use Countable;
use JsonSerializable;
use Override;
use stdClass;

/**
 * Ordered map representing a TOML table in the AST.
 *
 * Preserves insertion order and handles numeric string keys correctly
 * (PHP would coerce them to int in a plain array).
 *
 * @psalm-api
 */
final class TomlTable implements TomlNode, JsonSerializable, Countable
{
    /** @var list<array{string, TomlNode}> */
    private array $entries = [];

    /** @var array<string, int> key → index into for O(1) lookup */
    private array $index = [];

    public function __construct(
        public ?TableOrigin $origin = null,
    ) {
    }

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function set(string $key, TomlNode $value): void
    {
        if (isset($this->index[$key])) {
            $this->entries[$this->index[$key]] = [$key, $value];
        } else {
            $this->index[$key] = \count($this->entries);
            $this->entries[] = [$key, $value];
        }
    }

    public function get(string $key): ?TomlNode
    {
        if (!isset($this->index[$key])) {
            return null;
        }

        return $this->entries[$this->index[$key]][1];
    }

    public function has(string $key): bool
    {
        return isset($this->index[$key]);
    }

    public function isEmpty(): bool
    {
        return [] === $this->entries;
    }

    #[Override]
    public function count(): int
    {
        return \count($this->entries);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_map(static fn (array $entry): string => $entry[0], $this->entries);
    }

    /**
     * @return list<array{string, TomlNode}>
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Recursively convert to a plain PHP associative array.
     *
     * @return array<string, mixed>
     *
     * @psalm-suppress MixedAssignment
     */
    public function toAssocArray(): array
    {
        $result = [];
        foreach ($this->entries as [$key, $value]) {
            $result[$key] = self::unwrapNode($value);
        }

        return $result;
    }

    /**
     * Deep-clone this table and all nested TomlTable/TomlArray children.
     */
    public function deepClone(): self
    {
        $clone = new self($this->origin);
        foreach ($this->entries as [$key, $value]) {
            $clone->set($key, self::cloneNode($value));
        }

        return $clone;
    }

    /**
     * Create a TomlTable from an associative array of TomlNode values.
     *
     * @param array<string, TomlNode> $data
     */
    public static function fromAssoc(array $data, ?TableOrigin $origin = null): self
    {
        $table = new self($origin);
        foreach ($data as $key => $value) {
            /** @psalm-suppress RedundantCastGivenDocblockType PHP coerces numeric string keys to int */
            $table->set((string) $key, $value);
        }

        return $table;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        if ([] === $this->entries) {
            return new stdClass();
        }

        $obj = new stdClass();
        foreach ($this->entries as [$key, $value]) {
            $obj->{$key} = $value;
        }

        return $obj;
    }

    private static function unwrapNode(TomlNode $node): mixed
    {
        if ($node instanceof TomlValue) {
            return $node->value;
        }

        if ($node instanceof TomlArray) {
            return array_map(static fn (TomlNode $item): mixed => self::unwrapNode($item), $node->items);
        }

        if ($node instanceof self) {
            return $node->toAssocArray();
        }

        return $node;
    }

    private static function cloneNode(TomlNode $node): TomlNode
    {
        if ($node instanceof self) {
            return $node->deepClone();
        }

        if ($node instanceof TomlArray) {
            return new TomlArray(array_map(static fn (TomlNode $item): TomlNode => self::cloneNode($item), $node->items));
        }

        // TomlValue is readonly — no clone needed
        return $node;
    }
}
