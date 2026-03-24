<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use DateTimeInterface;
use HypnoTox\Toml\Encoder\TomlEncoder;
use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlNode;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use JsonException;
use Override;

/**
 * @psalm-immutable
 */
final class Toml implements TomlInterface
{
    public function __construct(
        private readonly TomlTable $root = new TomlTable(),
    ) {
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    #[Override]
    public function get(string $key): mixed
    {
        $keys = explode('.', $key);
        /** @var TomlNode|null $current */
        $current = $this->root;

        foreach ($keys as $k) {
            if (!$current instanceof TomlTable) {
                return null;
            }

            $current = $current->get($k);

            if (null === $current) {
                return null;
            }
        }

        if ($current instanceof TomlValue) {
            return $current->value;
        }

        return $current;
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    #[Override]
    public function set(string $key, TomlNode $value): self
    {
        $keys = explode('.', $key);
        $root = $this->root->deepClone();
        $current = $root;

        $lastIndex = \count($keys) - 1;
        for ($i = 0; $i < $lastIndex; ++$i) {
            $child = $current->get($keys[$i]);
            if (!$child instanceof TomlTable) {
                $child = new TomlTable();
                $current->set($keys[$i], $child);
            }
            $current = $child;
        }

        $current->set($keys[$lastIndex], $value);

        return new self($root);
    }

    /**
     * @return array<string, mixed>
     *
     * @psalm-suppress ImpureMethodCall
     */
    #[Override]
    public function toArray(): array
    {
        return $this->root->toAssocArray();
    }

    /**
     * @psalm-api
     *
     * @psalm-suppress ImpureMethodCall
     */
    #[Override]
    public function getData(): TomlTable
    {
        return $this->root;
    }

    /**
     * Returns JSON in the toml-test format with typed values.
     *
     * @throws JsonException
     *
     * @psalm-suppress ImpureMethodCall, ImpureFunctionCall
     */
    #[Override]
    public function toJson(): string
    {
        /** @psalm-suppress MixedAssignment */
        $converted = $this->convertToTestFormat($this->root);

        return $this->jsonEncodeValue($converted);
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    #[Override]
    public function toString(): string
    {
        return (new TomlEncoder())->encode($this);
    }

    /**
     * Create a Toml instance from a plain PHP array, inferring TOML types from PHP types.
     *
     * @param array<string, mixed> $data
     *
     * @psalm-suppress ImpureMethodCall, ImpureFunctionCall
     */
    #[Override]
    public static function fromArray(array $data): self
    {
        return new self(self::inferTable($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function inferTable(array $data): TomlTable
    {
        $table = new TomlTable();
        /** @var mixed $value */
        foreach ($data as $key => $value) {
            /** @psalm-suppress RedundantCastGivenDocblockType PHP coerces numeric string keys to int */
            $table->set((string) $key, self::inferValue($value));
        }

        return $table;
    }

    private static function inferValue(mixed $value): TomlNode
    {
        if (\is_string($value)) {
            return new TomlValue(ValueType::String, $value);
        }

        if (\is_int($value)) {
            return new TomlValue(ValueType::Integer, $value);
        }

        if (\is_float($value)) {
            return new TomlValue(ValueType::Float, $value);
        }

        if (\is_bool($value)) {
            return new TomlValue(ValueType::Bool, $value);
        }

        if ($value instanceof DateTimeInterface) {
            $formatted = $value->format('Y-m-d\\TH:i:s.uP');
            $formatted = preg_replace('/\\.0+(?=[+-Z])/', '', $formatted);

            return new TomlValue(ValueType::OffsetDateTime, $formatted);
        }

        if (\is_array($value)) {
            if ([] === $value) {
                return new TomlArray([]);
            }

            if (array_is_list($value)) {
                /** @var list<mixed> $value */
                return new TomlArray(array_map(static fn (mixed $item): TomlNode => self::inferValue($item), $value));
            }

            /** @var array<string, mixed> $value */
            return self::inferTable($value);
        }

        return new TomlValue(ValueType::String, (string) $value);
    }

    /**
     * @psalm-suppress ImpureFunctionCall
     */
    private function convertToTestFormat(TomlNode $data): mixed
    {
        if ($data instanceof TomlValue) {
            return $this->valueToTestFormat($data);
        }

        if ($data instanceof TomlArray) {
            return [
                '__is_toml_list__' => true,
                '__items__' => array_map(fn (TomlNode $item): mixed => $this->convertToTestFormat($item), $data->items),
            ];
        }

        if ($data instanceof TomlTable) {
            $result = ['__is_toml_table__' => true];
            foreach ($data->getEntries() as [$key, $value]) {
                $result['__entries__'][] = [$key, $this->convertToTestFormat($value)];
            }

            return $result;
        }

        return $data;
    }

    /**
     * Custom JSON encoder that handles TOML tables (always objects) and
     * TOML arrays (always arrays), including keys with NUL characters.
     */
    private function jsonEncodeValue(mixed $value): string
    {
        if (\is_array($value)) {
            if (isset($value['__is_toml_table__'])) {
                /** @var array<string, mixed> $value */
                return $this->jsonEncodeTable($value);
            }
            if (isset($value['__is_toml_list__'])) {
                /** @var list<mixed> $items */
                $items = $value['__items__'];

                return $this->jsonEncodeList($items);
            }

            /** @var array<string, mixed> $value */
            // Regular array (e.g., from valueToTestFormat: {type: ..., value: ...})
            return $this->jsonEncodeAssocArray($value);
        }

        // Scalar values
        return json_encode($value, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Encode a TOML table as a JSON object.
     *
     * @param array<string, mixed> $table
     */
    private function jsonEncodeTable(array $table): string
    {
        if (!isset($table['__entries__']) || [] === $table['__entries__']) {
            return '{}';
        }

        $parts = [];
        /** @var array{0: string, 1: mixed} $entry */
        foreach ($table['__entries__'] as $entry) {
            $key = $entry[0];
            /** @psalm-suppress MixedAssignment */
            $value = $entry[1];
            $encodedKey = json_encode($key, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            $encodedValue = $this->jsonEncodeValue($value);
            $parts[] = $encodedKey.':'.$encodedValue;
        }

        return '{'.implode(',', $parts).'}';
    }

    /**
     * Encode a TOML array as a JSON array.
     *
     * @param list<mixed> $items
     */
    private function jsonEncodeList(array $items): string
    {
        if ([] === $items) {
            return '[]';
        }

        $parts = [];
        /** @var mixed $item */
        foreach ($items as $item) {
            $parts[] = $this->jsonEncodeValue($item);
        }

        return '['.implode(',', $parts).']';
    }

    /**
     * Encode a regular associative array (like {type: ..., value: ...}).
     *
     * @param array<string, mixed> $arr
     */
    private function jsonEncodeAssocArray(array $arr): string
    {
        $parts = [];
        /** @var mixed $value */
        foreach ($arr as $key => $value) {
            $encodedKey = json_encode($key, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            $encodedValue = $this->jsonEncodeValue($value);
            $parts[] = $encodedKey.':'.$encodedValue;
        }

        return '{'.implode(',', $parts).'}';
    }

    /**
     * @return array{type: string, value: string}
     */
    private function valueToTestFormat(TomlValue $value): array
    {
        $stringValue = match ($value->type) {
            ValueType::String => (string) $value->value,
            ValueType::Integer => (string) $value->value,
            ValueType::Float => $this->formatFloatForTest($value->value),
            ValueType::Bool => $value->value ? 'true' : 'false',
            ValueType::OffsetDateTime,
            ValueType::LocalDateTime,
            ValueType::LocalDate,
            ValueType::LocalTime => (string) $value->value,
        };

        return [
            'type' => $value->type->value,
            'value' => $stringValue,
        ];
    }

    private function formatFloatForTest(mixed $value): string
    {
        if (\is_float($value)) {
            if (is_nan($value)) {
                return 'nan';
            }
            if (is_infinite($value)) {
                return $value > 0 ? 'inf' : '-inf';
            }

            // If the float value is an exact integer (no fractional part), output
            // without scientific notation to preserve round-trip fidelity.
            if (floor($value) === $value && abs($value) < 1e18) {
                $intVal = (int) $value;
                // Check it round-trips exactly
                if ((float) $intVal === $value) {
                    return (string) $intVal;
                }
            }

            // Use sprintf with full precision (17 significant digits)
            $str = \sprintf('%.17g', $value);

            // Ensure float representation always has a decimal point
            if (!str_contains($str, '.') && !str_contains($str, 'E') && !str_contains($str, 'e')) {
                $str .= '.0';
            }

            return $str;
        }

        return (string) $value;
    }
}
