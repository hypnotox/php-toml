<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

final class SeekerFactory implements SeekerFactoryInterface
{
    public function make(string $input, int $lineNumber = 1, int $lineOffset = 0): SeekerInterface
    {
        return new Seeker($input, $lineNumber, $lineOffset);
    }
}
