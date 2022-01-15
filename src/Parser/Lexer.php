<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Stream\StringStreamFactoryInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamFactoryInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;
use HypnoTox\Toml\Parser\Tokenizer\BasicStringTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\CommentTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\DatetimeTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\EndOfLineTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\FloatTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\IntegerTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\KeyTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\PunctuationTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\TokenizerInterface;

final class Lexer implements LexerInterface
{
    /**
     * @var TokenizerInterface[]
     */
    private readonly array $tokenizer;

    /**
     * @param TokenizerInterface[]|null $tokenizer
     */
    public function __construct(
        private StringStreamFactoryInterface $stringStreamFactory,
        private TokenStreamFactoryInterface $tokenStreamFactory,
        private TokenFactoryInterface $tokenFactory,
        array $tokenizer = null,
    ) {
        if (null !== $tokenizer) {
            $this->tokenizer = $tokenizer;
        } else {
            $this->tokenizer = [
                new CommentTokenizer($this->tokenFactory),
                new EndOfLineTokenizer($this->tokenFactory),
                new PunctuationTokenizer($this->tokenFactory),
                new DatetimeTokenizer($this->tokenFactory),
                new IntegerTokenizer($this->tokenFactory),
                new FloatTokenizer($this->tokenFactory),
                new BasicStringTokenizer($this->tokenFactory),
                new KeyTokenizer($this->tokenFactory),
            ];
        }
    }

    /**
     * @throws SyntaxException
     */
    public function tokenize(string $input): TokenStreamInterface
    {
        $tokenStream = $this->tokenStreamFactory->make();
        $stream = $this->stringStreamFactory->make($input);

        while (!$stream->isEOF()) {
            $lastPointer = $stream->getPointer();
            $stream->consumeWhitespace();

            foreach ($this->tokenizer as $tokenizer) {
                if ($tokenizer->tokenize($stream, $tokenStream)) {
                    continue 2;
                }
            }

            if ($stream->getPointer() === $lastPointer) {
                throw new SyntaxException(
                    sprintf(
                        'SyntaxError: %s on line %d:%d: "%s"',
                        'Could not parse input',
                        $stream->getLineNumber(),
                        $stream->getLineOffset() + 1,
                        $stream->peekUntilEOL(),
                    ),
                );
            }
        }

        return $tokenStream;
    }
}
