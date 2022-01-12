<?php

declare(strict_types=1);

namespace HypnoTox\Toml;

interface TomlFactoryInterface
{
    public function make(array $data = []): TomlInterface;
}