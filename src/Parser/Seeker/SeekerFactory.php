<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

final class SeekerFactory implements SeekerFactoryInterface
{
    public function make(string $input): SeekerInterface
    {
        return new Seeker($input);
    }
}
