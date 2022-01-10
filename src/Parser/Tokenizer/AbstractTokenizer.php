<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;

abstract class AbstractTokenizer implements TokenizerInterface
{
    public function __construct(
        protected TokenFactoryInterface $tokenFactory,
    ) {
    }

    /**
     * @throws SyntaxException
     */
    protected function raiseException(SeekerInterface $seeker, string $message): never
    {
        throw new SyntaxException(
            sprintf(
                'SyntaxError: %s on line %d offset %d ("%s")',
                $message,
                $seeker->getLineNumber(),
                $seeker->getLineOffset(),
                $seeker->peekUntilEOL(),
            ),
        );
    }
}
