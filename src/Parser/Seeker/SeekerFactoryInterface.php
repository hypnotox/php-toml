<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Seeker;

interface SeekerFactoryInterface
{
    public function make(string $input): SeekerInterface;
}
