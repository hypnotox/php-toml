<?php

declare(strict_types=1);

namespace Tests;

use HypnoTox\Toml\Builder\Builder;
use HypnoTox\Toml\Parser\Lexer;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Parser\Seeker\SeekerFactory;
use HypnoTox\Toml\Parser\Token\TokenFactory;
use HypnoTox\Toml\Parser\Token\TokenStreamFactory;

final class ParserTest extends BaseTest
{
    public function testCanParseToml(): void
    {
        $parser = new Parser(
            new Lexer(
                new SeekerFactory(),
                new TokenStreamFactory(),
                new TokenFactory(),
            ),
            new Builder(),
        );

        $input = file_get_contents(__DIR__.'/../Fixtures/valid/spec-example-1.toml');
        $toml = $parser->parse($input);

        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/valid/spec-example-1.json', $toml->toJson());
    }
}
