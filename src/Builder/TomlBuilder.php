<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Builder;

use HypnoTox\Toml\TomlFactoryInterface;
use HypnoTox\Toml\TomlInterface;

final class TomlBuilder implements TomlBuilderInterface
{
    public function __construct(
        private TomlFactoryInterface $factory,
        private array $data = [],
    ) {
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function set(string $key, mixed $value): TomlBuilderInterface
    {
        // TODO: Set according to key
        dd($key, $value);

        return $this;
    }

    public function build(): TomlInterface
    {
        return $this->factory->make($this->data);
    }
}
