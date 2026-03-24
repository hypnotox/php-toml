<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests;

use HypnoTox\Toml\Parser\TomlArray;
use HypnoTox\Toml\Parser\TomlNode;
use HypnoTox\Toml\Parser\TomlTable;
use HypnoTox\Toml\Parser\TomlValue;
use HypnoTox\Toml\Parser\ValueType;
use HypnoTox\Toml\Toml;
use JsonException;
use RuntimeException;
use stdClass;
use Throwable;

include __DIR__.'/../vendor/autoload.php';

$json = stream_get_contents(\STDIN);

try {
    // Decode as objects so we can distinguish {} (stdClass) from [] (array).
    // Fall back to associative array mode if object decode fails (e.g. null byte keys).
    try {
        $decoded = json_decode($json, false, 512, \JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $decoded = null;
    }

    if ($decoded instanceof stdClass) {
        $data = convertObject($decoded);
    } else {
        // Fallback: decode as associative array (loses {} vs [] distinction for empty)
        $decodedArray = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($decodedArray)) {
            exit(1);
        }

        $data = convertFromTestFormatArray($decodedArray);
    }

    $toml = new Toml($data);

    echo $toml->toString();
} catch (Throwable $e) {
    fwrite(\STDERR, $e->getMessage()."\n");
    exit(1);
}

exit(0);

/**
 * Convert a stdClass (JSON object) to a TomlTable for the internal model.
 */
function convertObject(stdClass $obj): TomlTable
{
    $props = get_object_vars($obj);

    // Check if this is a typed value: {"type": "...", "value": "..."}
    if (isTypedValue($props)) {
        // This shouldn't happen at top level, but handle it
        return new TomlTable();
    }

    $result = new TomlTable();
    foreach ($props as $key => $value) {
        $result->set((string) $key, convertValue($value));
    }

    return $result;
}

/**
 * Convert a single value from toml-test format to internal representation.
 */
function convertValue(mixed $value): TomlNode
{
    if ($value instanceof stdClass) {
        $props = get_object_vars($value);

        // Typed value: {"type": "...", "value": "..."}
        if (isTypedValue($props)) {
            return convertTypedValue($props['type'], $props['value']);
        }

        // TOML table (empty or non-empty object)
        $result = new TomlTable();
        foreach ($props as $k => $v) {
            $result->set((string) $k, convertValue($v));
        }

        return $result;
    }

    if (\is_array($value)) {
        // JSON array → TOML array
        /** @var list<TomlNode> $items */
        $items = array_map(static fn (mixed $item): TomlNode => convertValue($item), $value);

        return new TomlArray($items);
    }

    // Fallback for unexpected raw scalars
    return new TomlValue(ValueType::String, (string) $value);
}

/**
 * @param array<string, mixed> $data
 */
function isTypedValue(array $data): bool
{
    return 2 === \count($data)
        && isset($data['type'], $data['value'])
        && \is_string($data['type'])
        && \is_string($data['value']);
}

function convertTypedValue(string $type, string $value): TomlValue
{
    return match ($type) {
        'string' => new TomlValue(ValueType::String, $value),
        'integer' => new TomlValue(ValueType::Integer, (int) $value),
        'float' => new TomlValue(ValueType::Float, convertFloat($value)),
        'bool' => new TomlValue(ValueType::Bool, 'true' === $value),
        'datetime' => new TomlValue(ValueType::OffsetDateTime, $value),
        'datetime-local' => new TomlValue(ValueType::LocalDateTime, $value),
        'date-local' => new TomlValue(ValueType::LocalDate, $value),
        'time-local' => new TomlValue(ValueType::LocalTime, $value),
        default => throw new RuntimeException("Unknown type: {$type}"),
    };
}

function convertFloat(string $value): float
{
    return match ($value) {
        'inf', '+inf' => \INF,
        '-inf' => -\INF,
        'nan', '+nan', '-nan' => fdiv(0, 0),
        default => (float) $value,
    };
}

/**
 * Fallback: convert toml-test typed JSON (associative array mode) into internal data model.
 * Used when json_decode with objects fails (e.g. null byte property names).
 *
 * @param array<string, mixed> $data
 */
function convertFromTestFormatArray(array $data): TomlTable
{
    if (isTypedValue($data)) {
        return new TomlTable();
    }

    $result = new TomlTable();
    foreach ($data as $key => $value) {
        /** @psalm-suppress RedundantCastGivenDocblockType PHP coerces numeric string keys to int */
        $result->set((string) $key, convertValueArray($value));
    }

    return $result;
}

/**
 * Fallback value converter for associative array mode.
 */
function convertValueArray(mixed $value): TomlNode
{
    if (!\is_array($value)) {
        return new TomlValue(ValueType::String, (string) $value);
    }

    if (isTypedValue($value)) {
        return convertTypedValue($value['type'], $value['value']);
    }

    if (array_is_list($value)) {
        /** @var list<TomlNode> $items */
        $items = array_map(static fn (mixed $item): TomlNode => convertValueArray($item), $value);

        return new TomlArray($items);
    }

    $result = new TomlTable();
    foreach ($value as $k => $v) {
        /** @psalm-suppress RedundantCastGivenDocblockType PHP coerces numeric string keys to int */
        $result->set((string) $k, convertValueArray($v));
    }

    return $result;
}
