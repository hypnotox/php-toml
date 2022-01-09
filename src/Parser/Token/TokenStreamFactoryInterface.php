<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

interface TokenStreamFactoryInterface
{
    public function make(): TokenStreamInterface;
}
