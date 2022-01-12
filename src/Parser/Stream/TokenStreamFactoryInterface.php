<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Stream;

interface TokenStreamFactoryInterface
{
    public function make(): TokenStreamInterface;
}
