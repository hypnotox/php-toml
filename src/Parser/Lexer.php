<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Seeker\SeekerFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Tokenizer\CommentTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\DatetimeTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\EndOfLineTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\KeyTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\QuotedStringTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\TableHeadTokenizer;
use HypnoTox\Toml\Parser\Tokenizer\TokenizerInterface;

final class Lexer implements LexerInterface
{
    /**
     * @var TokenizerInterface[]
     */
    private readonly array $tokenizer;

    public function __construct(
        private SeekerFactoryInterface $seekerFactory,
        private TokenStreamFactoryInterface $tokenStreamFactory,
        private TokenFactoryInterface $tokenFactory,
    ) {
        $this->tokenizer = [
            new TableHeadTokenizer($this->tokenFactory),
            new KeyTokenizer($this->tokenFactory),
            new DatetimeTokenizer($this->tokenFactory),
            new QuotedStringTokenizer($this->tokenFactory),
            new CommentTokenizer($this->tokenFactory),
            new EndOfLineTokenizer($this->tokenFactory),
        ];
    }

    /**
     * @throws SyntaxException
     */
    public function tokenize(string $input): TokenStreamInterface
    {
        $tokenStream = $this->tokenStreamFactory->make();
        $seeker = $this->seekerFactory->make($input);
        $lastPointer = $seeker->getPointer();

        while (!$seeker->isEOF()) {
            $seeker->consumeWhitespace();

            foreach ($this->tokenizer as $tokenizer) {
                if ($tokenizer->tokenize($seeker, $tokenStream)) {
                    continue 2;
                }
            }

            if ($seeker->getPointer() === $lastPointer) {
                dump(
                    array_map(
                        fn (TokenInterface $token) => $token->getType()->name.': "'.str_replace("\n", '\n', $token->getValue()).'" ('.$token->getLine().':'.$token->getOffset().':'.\strlen($token->getValue()).')',
                        iterator_to_array($tokenStream),
                    ),
                );
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

            $lastPointer = $seeker->getPointer();
        }

        return $tokenStream;
    }
}
