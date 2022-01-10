<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Tokenizer;

use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class ArrayTokenizer extends AbstractTokenizer
{
    /**
     * @var TokenizerInterface[]
     */
    private readonly array $valueTokenizer;

    public function __construct(
        TokenFactoryInterface $tokenFactory
    ) {
        parent::__construct($tokenFactory);

        $this->valueTokenizer = [
            new QuotedStringTokenizer($tokenFactory),
        ];
    }

    public function tokenize(SeekerInterface $seeker, TokenStreamInterface $tokenStream): bool
    {
        if ('[' === $seeker->peek()) {
            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_ARRAY_START,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            $lastPointer = $seeker->getPointer();

            while (!$seeker->isEOF()) {
                $seeker->consumeWhitespace();

                foreach ($this->valueTokenizer as $tokenizer) {
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
                    $this->raiseException(
                        $seeker,
                        'Could not parse input',
                    );
                }

                $lastPointer = $seeker->getPointer();
            }

            $lineNumber = $seeker->getLineNumber();
            $lineOffset = $seeker->getLineOffset();

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_ARRAY_END,
                    $seeker->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            return true;
        }

        return false;
    }
}
