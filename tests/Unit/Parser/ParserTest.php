<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser;

use const DIRECTORY_SEPARATOR;
use Generator;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Tests\Unit\BaseTest;
use function in_array;

final class ParserTest extends BaseTest
{
    /**
//     * @dataProvider validInputProvider
     */
    public function testCanParseValidInput(Parser $parser, string $input, string $expectedJson): void
    {
        // TODO: Reactivate parser tests
        $this->expectNotToPerformAssertions();
//        $this->assertJsonStringEqualsJsonString($expectedJson, $parser->parse($input)->toJson());
    }

//    /**
//     * @dataProvider invalidInputProvider
//     */
//    public function testWillThrowOnInvalidInput(Parser $parser, string $input): void
//    {
//        $this->expectException(TomlExceptionInterface::class);
//        $parser->parse($input);
//    }

    public function getParser(): Parser
    {
        return new Parser();
    }

    public function validInputProvider(): Generator
    {
        $parser = $this->getParser();

        /** @var array $values */
        foreach ($this->generateFromDirectory(__DIR__.'/../../Fixtures/valid') as $values) {
            array_unshift($values, $parser);

            yield $values;
            break;
        }
    }

    public function invalidInputProvider(): Generator
    {
        $parser = $this->getParser();

        /** @var array $values */
        foreach ($this->generateFromDirectory(__DIR__.'/../../Fixtures/invalid', false) as $values) {
            array_unshift($values, $parser);

            yield $values;
        }
    }

    private function generateFromDirectory(string $directory, bool $withJson = true): Generator
    {
        $directoryIterator = scandir($directory);

        foreach ($directoryIterator as $value) {
            if (in_array($value, ['.', '..'])) {
                continue;
            }

            if (is_dir($directory.DIRECTORY_SEPARATOR.$value)) {
                yield from $this->generateFromDirectory($directory.DIRECTORY_SEPARATOR.$value, $withJson);
            } elseif (str_ends_with($value, '.toml')) {
                if ($withJson) {
                    yield [
                        file_get_contents($directory.DIRECTORY_SEPARATOR.$value),
                        file_get_contents($directory.DIRECTORY_SEPARATOR.str_replace('.toml', '.json', $value)),
                    ];
                } else {
                    yield [
                        file_get_contents($directory.DIRECTORY_SEPARATOR.$value),
                    ];
                }
            }
        }
    }
}
