#!/usr/bin/php
<?php

use HypnoTox\Toml\Builder\Builder;
use HypnoTox\Toml\Parser\Lexer;
use HypnoTox\Toml\Parser\Parser;

include 'vendor/autoload.php';

stream_set_blocking(\STDIN, false);
$data = stream_get_contents(\STDIN);

$parser = new Parser(
    new Lexer(),
    new Builder(),
);

try {
    return $parser->parse($data)->toJson();
} catch (Throwable $e) {
    exit(1);
}

exit(0);
