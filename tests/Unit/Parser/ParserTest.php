<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser;

use Generator;
use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ParserTest extends BaseTest
{
    #[DataProvider('validInputProvider')]
    public function testCanParseValidInput(string $input, string $expectedJson): void
    {
        $parser = new Parser();
        $result = $parser->parse($input);

        $actualJson = $result->toJson();

        $this->assertNormalizedJsonEquals($expectedJson, $actualJson);
    }

    #[DataProvider('invalidInputProvider')]
    public function testWillThrowOnInvalidInput(string $input): void
    {
        $this->expectException(TomlExceptionInterface::class);

        $parser = new Parser();
        $parser->parse($input);
    }

    public static function validInputProvider(): Generator
    {
        $directory = __DIR__.'/../../Fixtures/valid';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || 'toml' !== $file->getExtension()) {
                continue;
            }

            $tomlPath = $file->getRealPath();
            $jsonPath = preg_replace('/\.toml$/', '.json', $tomlPath);

            if (false === $tomlPath || false === $jsonPath || !file_exists($jsonPath)) {
                continue;
            }

            $tomlContent = file_get_contents($tomlPath);
            $jsonContent = file_get_contents($jsonPath);

            if (false === $tomlContent || false === $jsonContent) {
                continue;
            }

            $name = str_replace(
                [realpath($directory).\DIRECTORY_SEPARATOR, '.toml'],
                '',
                $tomlPath,
            );

            yield $name => [$tomlContent, $jsonContent];
        }
    }

    public static function invalidInputProvider(): Generator
    {
        $directory = __DIR__.'/../../Fixtures/invalid';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || 'toml' !== $file->getExtension()) {
                continue;
            }

            $tomlPath = $file->getRealPath();

            if (false === $tomlPath) {
                continue;
            }

            $tomlContent = file_get_contents($tomlPath);

            if (false === $tomlContent) {
                continue;
            }

            $name = str_replace(
                [realpath($directory).\DIRECTORY_SEPARATOR, '.toml'],
                '',
                $tomlPath,
            );

            yield $name => [$tomlContent];
        }
    }

    public function testHexIntegerOverflowThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse('val = 0xFFFFFFFFFFFFFFFF');
    }

    public function testOctIntegerOverflowThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse('val = 0o7777777777777777777777');
    }

    public function testBinIntegerOverflowThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse('val = 0b'.str_repeat('1', 64));
    }

    public function testHexIntegerMaxValidValue(): void
    {
        $result = (new Parser())->parse('val = 0x7FFFFFFFFFFFFFFF');
        $this->assertSame(\PHP_INT_MAX, $result->get('val'));
    }

    public function testUtf8BomIsStripped(): void
    {
        $bom = "\xEF\xBB\xBF";
        $result = (new Parser())->parse($bom.'key = "value"');
        $this->assertSame('value', $result->get('key'));
    }

    public function testTableDefWhenParentIsValueArrayThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse("a = [1]\n[a.b]");
    }

    public function testAotOnKeyThatIsValueThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse("a = 1\n[[a]]");
    }

    public function testAotNavigateThroughNonAotArrayThrows(): void
    {
        // a is a value array (not AoT), so [[a.c]] can't navigate through it
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse("a = [{b = 1}]\n[[a.c]]");
    }

    public function testExtendInlineTableViaAotThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse("a = {b = 1}\n[[a]]");
    }

    public function testExtendInlineTableViaDottedKeyThrows(): void
    {
        $this->expectException(SyntaxException::class);
        (new Parser())->parse("a = {b = 1}\na.c = 2");
    }

    public function testIntermediateDottedKeyConflictInInlineTable(): void
    {
        // a.b is a dotted key, then a = 2 tries to redefine intermediate 'a'
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('x = {a.b = 1, a = 2}');
    }

    public function testRedefinedKeyAsTableInInlineTable(): void
    {
        // a is defined as value, then a.b tries to use it as table
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('x = {a = 1, a.b = 2}');
    }

    public function testMissingCommaInInlineTableThrows(): void
    {
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('x = {a = 1 b = 2}');
    }

    public function testInvalidTimezoneOffsetThrows(): void
    {
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('val = 1979-05-27T07:32:00+25:00');
    }

    public function testFeb29OnNonLeapYearThrows(): void
    {
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('val = 2023-02-29T00:00:00');
    }

    public function testFeb29OnLeapYearSucceeds(): void
    {
        $result = (new Parser())->parse('val = 2024-02-29T00:00:00');
        $this->assertSame('2024-02-29T00:00:00', $result->get('val'));
    }

    public function testLeapYearDivisibleBy400(): void
    {
        $result = (new Parser())->parse('val = 2000-02-29T00:00:00');
        $this->assertSame('2000-02-29T00:00:00', $result->get('val'));
    }

    public function testNonLeapYearDivisibleBy100(): void
    {
        $this->expectException(SyntaxException::class);
        (new Parser())->parse('val = 1900-02-29T00:00:00');
    }

    public function testAotDefineWhenAlreadyDefinedAsExplicitTableThrows(): void
    {
        $this->expectException(TomlExceptionInterface::class);
        (new Parser())->parse("[a]\n[[a]]");
    }

    public function testAotWithExistingTableParentNavigatesChild(): void
    {
        // [a] defines table a, then [[a.b]] navigates through it (non-final key in AoT)
        $result = (new Parser())->parse("[a]\nkey = 1\n[[a.b]]\nval = 2");
        $this->assertSame(1, $result->get('a.key'));
    }

    public function testMultilineBasicStringWithCrlfPreserved(): void
    {
        // Valid CRLF in multiline basic string should parse without error
        $toml = "val = \"\"\"\r\nline1\r\nline2\"\"\"";
        $result = (new Parser())->parse($toml);
        $this->assertStringContainsString('line1', (string) $result->get('val'));
    }

    public function testMultilineLiteralStringWithCrlfPreserved(): void
    {
        // Valid CRLF in multiline literal string should parse without error
        $toml = "val = '''\r\nline1\r\nline2'''";
        $result = (new Parser())->parse($toml);
        $this->assertStringContainsString('line1', (string) $result->get('val'));
    }

    /**
     * Compares two JSON strings, normalizing typed values so that
     * precision differences for floats and trailing zeros in datetimes are treated as equal.
     */
    private function assertNormalizedJsonEquals(string $expectedJson, string $actualJson): void
    {
        $expected = json_decode($expectedJson, true, 512, \JSON_THROW_ON_ERROR);
        $actual = json_decode($actualJson, true, 512, \JSON_THROW_ON_ERROR);

        $expected = $this->normalizeTypedValues($expected);
        $actual = $this->normalizeTypedValues($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Recursively walks a decoded JSON structure and normalizes typed values.
     *
     * - Floats: converts string values to PHP floats for numeric comparison,
     *   and normalizes special values (+inf -> inf).
     * - Datetimes: strips trailing zeros from fractional seconds.
     */
    private function normalizeTypedValues(mixed $data): mixed
    {
        if (!\is_array($data)) {
            return $data;
        }

        /** @var array<string, mixed> $data */
        if (isset($data['type'], $data['value']) && \is_string($data['type']) && \is_string($data['value'])) {
            return $this->normalizeTypedValue($data['type'], $data['value']);
        }

        $result = [];
        /** @var mixed $value */
        foreach ($data as $key => $value) {
            /** @psalm-suppress MixedAssignment */
            $result[$key] = $this->normalizeTypedValues($value);
        }

        return $result;
    }

    /**
     * @return array{type: string, value: mixed}
     */
    private function normalizeTypedValue(string $type, string $value): array
    {
        if ('float' === $type) {
            return ['type' => $type, 'value' => $this->normalizeFloat($value)];
        }

        if (\in_array($type, ['datetime', 'datetime-local'], true)) {
            return ['type' => $type, 'value' => $this->normalizeDatetime($value)];
        }

        return ['type' => $type, 'value' => $value];
    }

    private function normalizeFloat(string $value): mixed
    {
        $normalized = str_replace('+inf', 'inf', $value);
        $normalized = str_replace('+nan', 'nan', $normalized);
        $normalized = str_replace('-nan', 'nan', $normalized);

        if (\in_array($normalized, ['nan', 'inf', '-inf'], true)) {
            return $normalized;
        }

        return (float) $normalized;
    }

    /**
     * Normalize datetime fractional seconds by removing trailing zeros.
     * E.g. "17:45:56.6000Z" -> "17:45:56.6Z", "17:45:56.6000+08:00" -> "17:45:56.6+08:00".
     */
    private function normalizeDatetime(string $value): string
    {
        return (string) preg_replace('/(\.\d+?)0+(Z|[+-])/', '$1$2', $value);
    }
}
