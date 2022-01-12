<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Seeker\SeekerFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Tokenizer\BasicStringTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\CommentTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\DatetimeTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\DottedStringTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\EndOfLineTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\FloatTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\IntegerTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\LiteralStringTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\PunctuationTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\QuotedStringTokenizer;
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
        private SeekerFactoryInterface $seekerFactory,
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
                new QuotedStringTokenizer($this->tokenFactory),
                new LiteralStringTokenizer($this->tokenFactory),
                new DottedStringTokenizer($this->tokenFactory),
                new BasicStringTokenizer($this->tokenFactory),
            ];
        }
    }

    /**
     * @throws SyntaxException
     */
    public function tokenize(string $input): TokenStreamInterface
    {
        $tokenStream = $this->tokenStreamFactory->make();
        $seeker = $this->seekerFactory->make($input);

        while (!$seeker->isEOF()) {
            $lastPointer = $seeker->getPointer();
            $seeker->consumeWhitespace();

            foreach ($this->tokenizer as $tokenizer) {
                if ($tokenizer->tokenize($seeker, $tokenStream)) {
                    continue 2;
                }
            }

            if ($seeker->getPointer() === $lastPointer) {
                throw new SyntaxException(
                    sprintf(
                        'SyntaxError: %s on line %d:%d: "%s"',
                        'Could not parse input',
                        $seeker->getLineNumber(),
                        $seeker->getLineOffset() + 1,
                        $seeker->peekUntilEOL(),
                    ),
                );
            }
        }

        return $tokenStream;
    }
}
