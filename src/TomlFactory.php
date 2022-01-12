<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

class TomlFactory implements TomlFactoryInterface
{
    public function make(array $data = []): TomlInterface
    {
        return new Toml($data);
    }
}