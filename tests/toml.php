<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests;

use HypnoTox\Toml\Parser\Parser;
use Throwable;

include __DIR__.'/../vendor/autoload.php';

$data = stream_get_contents(\STDIN);

$parser = new Parser();

try {
    echo $parser->parse($data)->toJson();
} catch (Throwable $e) {
    exit(1);
}

exit(0);
