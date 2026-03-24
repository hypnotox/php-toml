<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Encoder;

use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlNode;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use HypnoTox\Toml\TomlInterface;
use Override;

/**
 * @psalm-api
 */
final class TomlEncoder implements TomlEncoderInterface
{
    private const int INLINE_MAX_LENGTH = 80;

    #[Override]
    public function encode(TomlInterface $toml): string
    {
        $data = $toml->getData();
        $lines = [];

        $this->encodeTable($data, [], $lines);

        $result = implode("\n", $lines);

        // Ensure file ends with a single newline
        if ('' !== $result) {
            $result = rtrim($result, "\n")."\n";
        }

        return $result;
    }

    /**
     * Encode a table into TOML lines.
     *
     * @param list<string> $path
     * @param list<string> $lines
     */
    private function encodeTable(TomlTable $data, array $path, array &$lines): void
    {
        /** @var list<array{string, TomlNode}> $scalars */
        $scalars = [];
        /** @var list<array{string, TomlTable}> $tables */
        $tables = [];
        /** @var list<array{string, TomlArray}> $arraysOfTables */
        $arraysOfTables = [];

        foreach ($data->getEntries() as [$key, $value]) {
            if ($value instanceof TomlValue) {
                $scalars[] = [$key, $value];
            } elseif ($value instanceof TomlArray) {
                if ($this->isArrayOfTables($value)) {
                    $arraysOfTables[] = [$key, $value];
                } else {
                    $scalars[] = [$key, $value];
                }
            } elseif ($value instanceof TomlTable) {
                $tables[] = [$key, $value];
            }
        }

        // Emit scalar key-value pairs
        foreach ($scalars as [$key, $value]) {
            $lines[] = $this->formatKey($key).' = '.$this->formatValue($value);
        }

        // Emit sub-tables
        foreach ($tables as [$key, $value]) {
            $subPath = [...$path, $key];

            if ([] !== $lines) {
                $lines[] = '';
            }

            $lines[] = '['.$this->formatKeyPath($subPath).']';
            $this->encodeTable($value, $subPath, $lines);
        }

        // Emit arrays of tables
        foreach ($arraysOfTables as [$key, $value]) {
            $subPath = [...$path, $key];

            foreach ($value->items as $item) {
                if ([] !== $lines) {
                    $lines[] = '';
                }

                $lines[] = '[['.$this->formatKeyPath($subPath).']]';

                if ($item instanceof TomlTable) {
                    $this->encodeTable($item, $subPath, $lines);
                }
            }
        }
    }

    /**
     * Determine whether a TomlArray is an array of tables (each item is a TomlTable).
     */
    private function isArrayOfTables(TomlArray $array): bool
    {
        if ([] === $array->items) {
            return false;
        }

        foreach ($array->items as $item) {
            if (!$item instanceof TomlTable) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format a single key: bare if possible, quoted otherwise.
     */
    private function formatKey(string $key): string
    {
        if (1 === preg_match('/\A[A-Za-z0-9_-]+\z/', $key)) {
            return $key;
        }

        return $this->encodeBasicString($key);
    }

    /**
     * Format a dotted key path.
     *
     * @param list<string> $path
     */
    private function formatKeyPath(array $path): string
    {
        return implode('.', array_map(fn (string $key): string => $this->formatKey($key), $path));
    }

    /**
     * Format any value for TOML output.
     */
    private function formatValue(TomlNode $value): string
    {
        if ($value instanceof TomlValue) {
            return $this->formatTomlValue($value);
        }

        if ($value instanceof TomlArray) {
            return $this->formatArray($value);
        }

        if ($value instanceof TomlTable) {
            return $this->formatInlineTable($value);
        }

        return '';
    }

    /**
     * Format a typed TomlValue for output.
     */
    private function formatTomlValue(TomlValue $value): string
    {
        return match ($value->type) {
            ValueType::String => $this->encodeBasicString((string) $value->value),
            ValueType::Integer => (string) $value->value,
            ValueType::Float => $this->formatFloat($value->value),
            ValueType::Bool => $value->value ? 'true' : 'false',
            ValueType::OffsetDateTime,
            ValueType::LocalDateTime,
            ValueType::LocalDate,
            ValueType::LocalTime => (string) $value->value,
        };
    }

    /**
     * Format a float value, handling special values (inf, nan).
     */
    private function formatFloat(mixed $value): string
    {
        if (!\is_float($value)) {
            return (string) $value;
        }

        if (is_nan($value)) {
            return 'nan';
        }

        if (is_infinite($value)) {
            return $value > 0 ? 'inf' : '-inf';
        }

        $str = \sprintf('%.17g', $value);

        // Ensure float always has a decimal point for TOML
        if (!str_contains($str, '.') && !str_contains($str, 'e') && !str_contains($str, 'E')) {
            $str .= '.0';
        }

        return $str;
    }

    /**
     * Format a TOML array.
     */
    private function formatArray(TomlArray $array): string
    {
        if ([] === $array->items) {
            return '[]';
        }

        // Check if any item is complex (nested table or non-empty array)
        $hasComplex = false;
        foreach ($array->items as $item) {
            if ($item instanceof TomlTable || ($item instanceof TomlArray && [] !== $item->items)) {
                $hasComplex = true;

                break;
            }
        }

        /** @var list<string> $formatted */
        $formatted = array_map(fn (TomlNode $item): string => $this->formatValue($item), $array->items);

        $inline = '['.implode(', ', $formatted).']';

        if (!$hasComplex && \strlen($inline) <= self::INLINE_MAX_LENGTH) {
            return $inline;
        }

        // Multiline array
        $lines = ['['];
        foreach ($formatted as $item) {
            $lines[] = '    '.$item.',';
        }
        $lines[] = ']';

        return implode("\n", $lines);
    }

    /**
     * Format an inline table.
     */
    private function formatInlineTable(TomlTable $data): string
    {
        if ($data->isEmpty()) {
            return '{}';
        }

        $parts = [];
        foreach ($data->getEntries() as [$key, $value]) {
            $parts[] = $this->formatKey($key).' = '.$this->formatValue($value);
        }

        return '{'.implode(', ', $parts).'}';
    }

    /**
     * Encode a string as a TOML basic string with proper escaping.
     */
    private function encodeBasicString(string $value): string
    {
        $escaped = '';

        $length = \strlen($value);
        for ($i = 0; $i < $length; ++$i) {
            $char = $value[$i];
            $ord = \ord($char);

            $escaped .= match (true) {
                '\\' === $char => '\\\\',
                '"' === $char => '\\"',
                "\n" === $char => '\\n',
                "\r" === $char => '\\r',
                "\t" === $char => '\\t',
                "\x08" === $char => '\\b',
                "\x0C" === $char => '\\f',
                $ord < 0x20, 0x7F === $ord => \sprintf('\\u%04X', $ord),
                default => $char,
            };
        }

        return '"'.$escaped.'"';
    }
}
