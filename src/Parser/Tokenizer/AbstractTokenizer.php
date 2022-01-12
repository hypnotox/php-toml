<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
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
    protected function raiseException(StringStreamInterface $stream, string $message): never
    {
        throw new SyntaxException(
            sprintf(
                'SyntaxError: %s on line %d at offset %d: "%s"',
                $message,
                $stream->getLineNumber(),
                $stream->getLineOffset() + 1,
                $stream->peekUntilEOL(),
            ),
        );
    }
}
