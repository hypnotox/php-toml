<?php

declare(strict_types=1);

namespace Tests;

use const DIRECTORY_SEPARATOR;
use Generator;
use HypnoTox\Toml\Builder\TomlBuilder;
use HypnoTox\Toml\Parser\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Parser\Lexer;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Parser\Stream\StringStreamFactory;
use HypnoTox\Toml\Parser\Stream\TokenStreamFactory;
use HypnoTox\Toml\Parser\Token\TokenFactory;
use HypnoTox\Toml\TomlFactory;
use function in_array;

final class ParserTest extends BaseTest
{
    /**
     * @dataProvider validInputProvider
     */
    public function testCanParseValidInput(Parser $parser, string $input, string $expectedJson): void
    {
        $this->assertJsonStringEqualsJsonString($expectedJson, $parser->parse($input)->toJson());
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
        return new Parser(
            new Lexer(
                new StringStreamFactory(),
                new TokenStreamFactory(),
                new TokenFactory(),
            ),
            new TomlBuilder(
                new TomlFactory(),
            ),
        );
    }

    public function validInputProvider(): Generator
    {
        /** @var array $values */
        foreach ($this->generateFromDirectory(__DIR__.'/../Fixtures/valid') as $values) {
            dump($values);
            array_unshift($values, $this->getParser());

            yield $values;
            break;
        }
    }

    public function invalidInputProvider(): Generator
    {
        /** @var array $values */
        foreach ($this->generateFromDirectory(__DIR__.'/../Fixtures/invalid', false) as $values) {
            array_unshift($values, $this->getParser());

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
