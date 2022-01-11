#!/usr/bin/php
<?php

use HypnoTox\Toml\Builder\Builder;
use HypnoTox\Toml\Parser\Lexer;
use HypnoTox\Toml\Parser\Parser;
use HypnoTox\Toml\Parser\Seeker\SeekerFactory;
use HypnoTox\Toml\Parser\Token\TokenFactory;
use HypnoTox\Toml\Parser\Token\TokenStreamFactory;

include 'vendor/autoload.php';

stream_set_blocking(STDIN, false);
$data = stream_get_contents(STDIN);

$parser = new Parser(
		new Lexer(
				new SeekerFactory(),
				new TokenStreamFactory(),
				new TokenFactory(),
		),
		new Builder(),
);

try {
	echo $parser->parse($data)->toJson();
} catch (Throwable $e) {
	exit(1);
}

exit(0);
