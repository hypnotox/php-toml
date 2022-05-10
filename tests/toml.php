<?php

declare(strict_types=1);

use HypnoTox\Toml\Builder\TomlBuilder;
use HypnoTox\Toml\Lexer\Lexer;
use HypnoTox\Toml\Lexer\Stream\TokenStreamFactory;
use HypnoTox\Toml\Lexer\Token\TokenFactory;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Stream\StringStreamFactory;
use HypnoTox\Toml\TomlFactory;

include 'vendor/autoload.php';

stream_set_blocking(\STDIN, false);
$data = stream_get_contents(\STDIN);

$parser = new Parser(
    new Lexer(
        new StringStreamFactory(),
        new TokenStreamFactory(),
        new TokenFactory(),
    ),
    new TomlBuilder(
        new TomlFactory(),
    ),
);

try {
    echo $parser->parse($data)->toJson();
} catch (Throwable $e) {
    exit(1);
}

exit(0);
