<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;

interface TokenizerInterface
{
    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool;
}
