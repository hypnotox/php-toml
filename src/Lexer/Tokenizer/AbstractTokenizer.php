<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Lexer\Token\TokenFactoryInterface;
use HypnoTox\Toml\Stream\StringStreamInterface;

abstract class AbstractTokenizer implements TokenizerInterface
{
    public function __construct(
        protected TokenFactoryInterface $tokenFactory,
    ) {
    }

    /**
     * @throws \HypnoTox\Toml\Exception\SyntaxException
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
