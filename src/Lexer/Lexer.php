<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Lexer\Stream\TokenStreamFactoryInterface;
use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenFactoryInterface;
use HypnoTox\Toml\Lexer\Tokenizer\BasicStringTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\CommentTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\DatetimeTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\EndOfLineTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\FloatTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\IntegerTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\KeyTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\PunctuationTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\TokenizerInterface;
use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Stream\StringStreamFactoryInterface;

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
