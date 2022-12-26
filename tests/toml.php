<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests;

use HypnoTox\Toml\Parser\Lexer\Lexer;
use HypnoTox\Toml\Parser\Parser;
use Throwable;

include 'vendor/autoload.php';

stream_set_blocking(\STDIN, false);
$data = stream_get_contents(\STDIN);

$parser = new Parser(
    new Lexer(),
);

try {
    echo $parser->parse($data)->toJson();
} catch (Throwable $e) {
    exit(1);
}

exit(0);
