<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Exception;

use Exception;

abstract class AbstractParserException extends Exception implements TomlExceptionInterface
{
}
