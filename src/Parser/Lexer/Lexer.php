<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Token\Token;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

/**
 * @internal
 */
final class Lexer implements LexerInterface
{
    /**
     * @throws EncodingException|UnableToParseInputException
     */
    public function tokenize(string|StringStream $input): array
    {
        /** @var TokenInterface[] $tokens */
        $tokens = [];
        $stream = $input instanceof StringStream ? $input : new StringStream($input);
        $mode = LexerMode::LINE_START;
        $row = 1;
        $column = 1;

        while (true) {
            $expectedTokens = match ($mode) {
                LexerMode::LINE_START => [
                    ...$this->getKeyTokens(),
                    TokenType::T_NEWLINE,
                    TokenType::T_WHITESPACE,
                    TokenType::T_COMMENT,
                    TokenType::T_EOF,
                ],
                LexerMode::KEY => [
                    ...$this->getKeyTokens(),
                    TokenType::T_WHITESPACE,
                    TokenType::T_DOT,
                    TokenType::T_EQUALS,
                ],
                LexerMode::VALUE => [
                    ...$this->getValueTokens(),
                    TokenType::T_WHITESPACE,
                ],
                LexerMode::LINE_END => [
                    TokenType::T_NEWLINE,
                    TokenType::T_WHITESPACE,
                    TokenType::T_COMMENT,
                    TokenType::T_EOF,
                ],
            };

            foreach ($expectedTokens as $tokenType) {
                if ($tokenType->matches($stream)) {
                    $value = $stream->consumeMatching($tokenType);

                    if ($tokenType->shouldAddToken()) {
                        $tokens[] = new Token(
                            $tokenType,
                            $value,
                            $row,
                            $column,
                        );
                    }

                    $column += mb_strlen($value);

                    $mode = match ($tokenType) {
                        TokenType::T_NEWLINE => LexerMode::LINE_START,
                        TokenType::T_KEY, TokenType::T_QUOTED_KEY => LexerMode::KEY,
                        TokenType::T_EQUALS => LexerMode::VALUE,
                        TokenType::T_FLOAT,
                        TokenType::T_INTEGER,
                        TokenType::T_BASIC_STRING,
                        TokenType::T_QUOTED_STRING, => LexerMode::LINE_END,
                        default => $mode,
                    };

                    if (TokenType::T_NEWLINE === $tokenType) {
                        ++$row;
                        $column = 1;
                    }

                    if (TokenType::T_EOF === $tokenType) {
                        break 2;
                    }

                    continue 2;
                }
            }

            $this->unableToTokenize($stream, $expectedTokens);
        }

        return $tokens;
    }

    /**
     * @param TokenType[] $expectedTokens
     *
     * @throws UnableToParseInputException
     */
    private function unableToTokenize(StringStreamInterface $stream, array $expectedTokens): never
    {
        $unableToTokenize = $stream->peekMatching('(.*)');

        if (mb_strlen($unableToTokenize) > 100) {
            $unableToTokenize = mb_substr($unableToTokenize, 0, 100).'[...]';
        }

        $unableToTokenize = '\''.$unableToTokenize.'\'';

        foreach (TokenType::cases() as $tokenType) {
            if ($tokenType->matches($stream)) {
                $unableToTokenize = $tokenType->name;
            }
        }

        $expected = implode(
            ', ',
            array_map(
                static fn (TokenType $tokenType): string => $tokenType->name,
                $expectedTokens
            ),
        );

        throw new UnableToParseInputException("Unexpected $unableToTokenize, expected one of: $expected");
    }

    /**
     * @return TokenType[]
     */
    public function getKeyTokens(): array
    {
        return [
            TokenType::T_KEY,
            TokenType::T_QUOTED_KEY,
        ];
    }

    /**
     * @return TokenType[]
     */
    public function getValueTokens(): array
    {
        return [
            TokenType::T_FLOAT,
            TokenType::T_INTEGER,
            TokenType::T_BASIC_STRING,
            TokenType::T_QUOTED_STRING,
        ];
    }
}
