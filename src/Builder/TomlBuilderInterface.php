<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Builder;

use HypnoTox\Toml\TomlFactoryInterface;
use HypnoTox\Toml\TomlInterface;

interface TomlBuilderInterface
{
    public function __construct(TomlFactoryInterface $factory, array $data = []);

    public function setData(array $data): TomlBuilderInterface;

    public function set(string $key, mixed $value): TomlBuilderInterface;

    public function build(): TomlInterface;
}