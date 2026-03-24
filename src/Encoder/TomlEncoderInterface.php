<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Encoder;

use HypnoTox\Toml\TomlInterface;

/**
 * @psalm-api
 */
interface TomlEncoderInterface
{
    public function encode(TomlInterface $toml): string;
}
