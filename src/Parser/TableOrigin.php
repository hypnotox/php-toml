<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

/**
 * Describes how a TomlTable was created during parsing.
 *
 * @psalm-api
 */
enum TableOrigin
{
    case Explicit;
    case Implicit;
    case ImplicitDotted;
    case Inline;
    case ArrayOfTables;
}
