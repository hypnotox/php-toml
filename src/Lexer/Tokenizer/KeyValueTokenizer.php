<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer\Tokenizer;

use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenFactoryInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Lexer\Tokenizer\Value\BasicStringTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\Value\DatetimeTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\Value\FloatTokenizer;
use HypnoTox\Toml\Lexer\Tokenizer\Value\IntegerTokenizer;
use HypnoTox\Toml\Stream\StringStreamFactoryInterface;
use HypnoTox\Toml\Stream\StringStreamInterface;
use function strlen;

final class KeyValueTokenizer extends AbstractTokenizer
{
    /**
     * @var TokenizerInterface[]
     */
    private readonly array $valueTokenizer;

    /**
     * @param TokenizerInterface[] $valueTokenizer
     */
    public function __construct(
        TokenFactoryInterface $tokenFactory,
        private StringStreamFactoryInterface $streamFactory,
        array $valueTokenizer = null,
    ) {
        parent::__construct($tokenFactory);

        if ($valueTokenizer) {
            $this->valueTokenizer = $valueTokenizer;
        } else {
            $this->valueTokenizer = [
                new CommentTokenizer($this->tokenFactory),
                new EndOfLineTokenizer($this->tokenFactory),
                new PunctuationTokenizer($this->tokenFactory),
                new DatetimeTokenizer($this->tokenFactory),
                new IntegerTokenizer($this->tokenFactory),
                new FloatTokenizer($this->tokenFactory),
                new BasicStringTokenizer($this->tokenFactory),
            ];
        }
    }

    public function tokenize(StringStreamInterface $stream, TokenStreamInterface $tokenStream): bool
    {
        if (0 === $stream->getLineOffset()) {
            $lineNumber = $stream->getLineNumber();
            $lineOffset = $stream->getLineOffset();
            $string = $stream->peekUntilOneOf(['=', ',', StringStreamInterface::EOL, StringStreamInterface::COMMENT]);
            $stream->consume(strlen($string));

            if (StringStreamInterface::EOL === $stream->peek()) {
                $this->raiseException($stream, 'Unexpected T_RETURN "\n", expected T_EQUALS "="');
            }

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_KEY,
                    trim($string),
                    $lineNumber,
                    $lineOffset,
                )
            );

            $tokenStream->addToken(
                $this->tokenFactory->make(
                    TokenType::T_EQUALS,
                    $stream->consume(),
                    $lineNumber,
                    $lineOffset,
                )
            );

            $stream->consumeWhitespace();

            $input = match (true) {
                '[' === $stream->peek() => $this->peekArray($stream),
                default                 => trim($stream->peekUntilEOS()),
            };

            if ('' === $input) {
                $this->raiseException(
                    $stream,
                    'Unexpected T_RETURN "\n", expected value',
                );
            }

            $valueStream = $this->streamFactory->make($input, $stream->getLineNumber(), $stream->getLineOffset());

            while (!$valueStream->isEOF()) {
                $lastPointer = $valueStream->getPointer();
                $valueStream->consumeWhitespace();

                foreach ($this->valueTokenizer as $tokenizer) {
                    if ($tokenizer->tokenize($valueStream, $tokenStream)) {
                        continue 2;
                    }
                }

                if ($valueStream->getPointer() === $lastPointer) {
                    $this->raiseException(
                        $stream,
                        'Could not parse input',
                    );
                }
            }

            $stream->consume($valueStream->getInputLength());

            return true;
        }

        return false;
    }

    /**
     * @throws SyntaxException
     */
    private function peekArray(StringStreamInterface $stream): string
    {
        $input = $stream->peekUntil(']', true);

        if (!str_ends_with($input, ']')) {
            if ($stream->getPointer() + strlen($input) === $stream->getInputLength()) {
                $this->raiseException(
                    $stream,
                    'Unexpected end of file, expected T_BRACKET_CLOSE',
                );
            }

            $unexpectedChar = substr($input, -1);

            $this->raiseException(
                $stream,
                sprintf(
                    'Unexpected "%s", expected T_BRACKET_CLOSE',
                    $unexpectedChar
                ),
            );
        }

        return $input;
    }
}
