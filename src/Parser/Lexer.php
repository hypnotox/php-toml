<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Seeker\SeekerFactoryInterface;
use HypnoTox\Toml\Parser\Seeker\SeekerInterface;
use HypnoTox\Toml\Parser\Token\TokenFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamFactoryInterface;
use HypnoTox\Toml\Parser\Token\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

final class Lexer implements LexerInterface
{
    private const EXPRESSION = [];

    public function __construct(
        private SeekerFactoryInterface $seekerFactory,
        private TokenStreamFactoryInterface $tokenStreamFactory,
        private TokenFactoryInterface $tokenFactory,
    ) {
    }

    /**
     * TODO: Reimplement tokenizer without RegEx lookaheads since the format is fairly easy to read without it.
     * @throws SyntaxException
     */
    public function tokenize(string $input): TokenStreamInterface
    {
        $tokenStream = $this->tokenStreamFactory->make();
        $seeker = $this->seekerFactory->make($input);
        $lastPointer = $seeker->getPointer();

        while (!$seeker->isEOF()) {
            // Find whitespace characters, including first EOL
            $seeker->consumeWhitespace();

            if (0 === $seeker->getLineOffsetWithoutPrecedingWhitespace() && '[' === $seeker->peek()) {
                $matches = $this->seekUntilWhitespace($seeker, ']');

                if (!$matches) {
                    $this->raiseException($seeker, 'Expected table name');
                }

                $tableName = $seeker->consume($matches[0]['length']);
                $this->addTokenToStream(
                    TokenType::T_TABLE_HEADER,
                    $tableName,
                    $tokenStream,
                    $seeker,
                );
                continue;
            }

            if ('#' === $seeker->peek()) {
                $seeker->consumeUntilEOL();
                continue;
            }

            /*
             * Must be key / value pair after this
             */

            // Tokenize key
            $matches = $seeker->seek('~^([A-Za-z0-9_-]+)~');

            if (!$matches || 2 !== \count($matches)) {
                $this->raiseException($seeker, 'Expected one of T_BASIC_STRING|T_QUOTED_STRING|T_LITERAL_STRING');
            }

            $keyType = match ($seeker->peek()) {
                default   => TokenType::T_BASIC_STRING,
                '"'       => TokenType::T_QUOTED_STRING,
                '\''      => TokenType::T_LITERAL_STRING,
            };

            $this->addTokenToStream(
                $keyType,
                $matches[1]['value'],
                $tokenStream,
                $seeker,
            );

            $seeker->consume($matches[0]['length']);
            $seeker->consumeWhitespace();

            if ('=' !== $seeker->consume()) {
                $this->raiseException($seeker, 'Expected T_EQUALS');
            }

            $seeker->consumeWhitespace();
            $this->addTokenToStream(
                TokenType::T_EQUALS,
                '=',
                $tokenStream,
                $seeker,
            );

            // Find and tokenize datetime
            if ($matches = $seeker->seek('~^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z([+\-]\d{2}:\d{2})?)\s*\n~')) {
                $this->addTokenToStream(
                    TokenType::T_DATETIME,
                    $matches[1]['value'],
                    $tokenStream,
                    $seeker,
                );

                $seeker->consumeUntilEOL();
                continue;
            }

            // Find and tokenize array value
            if ($matches = $seeker->seek('~^(\[.*])\s*~')) {
                $this->addTokenToStream(
                    TokenType::T_DATETIME,
                    $matches[1]['value'],
                    $tokenStream,
                    $seeker,
                );

                $seeker->consumeUntilEOL();
                continue;
            }

            // Find and tokenize string value
            if ($matches = $seeker->seek('~^(\w*)\s*~')) {
                $keyType = match ($seeker->peek()) {
                    default   => TokenType::T_BASIC_STRING,
                    '"'       => TokenType::T_QUOTED_STRING,
                    '\''      => TokenType::T_LITERAL_STRING,
                };

                $this->addTokenToStream(
                    $keyType,
                    $matches[1]['value'],
                    $tokenStream,
                    $seeker,
                );

                $seeker->consumeUntilEOL();
                continue;
            }

            if ($seeker->getPointer() === $lastPointer) {
                $this->raiseException($seeker, 'Could not parse input');
            }

            $lastPointer = $seeker->getPointer();
        }

        return $tokenStream;
    }

    /**
     * @param string[] $allowedPatterns
     */
    public function seekWithPatterns(SeekerInterface $seeker, array $allowedPatterns): array
    {
        return $seeker->seek(
            sprintf(
            /** @lang RegExp */
                '~^%s\s*~',
                implode('|', $allowedPatterns),
            ),
        );
    }

    /**
     * @return array<array{offset: int, length: int, value: string}>|false
     */
    private function seekUntilWhitespace(SeekerInterface $seeker, string $denominator = ''): array|false
    {
        $matches = $seeker->seek(
            sprintf(
                '~^[^\s]*%s~',
                $denominator,
            ),
        );

        if (0 === \count($matches) || 0 === $matches[0]['length']) {
            return false;
        }

        return $matches;
    }

    private function addTokenToStream(TokenType $type, string $value, TokenStreamInterface $tokenStream, SeekerInterface $seeker): void
    {
        $tokenStream->addToken(
            $this->tokenFactory->make(
                $type,
                $value,
                $seeker->getLineNumber(),
                $seeker->getLineOffset(),
            )
        );
    }

    /**
     * @throws SyntaxException
     */
    private function raiseException(SeekerInterface $seeker, string $message): never
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
