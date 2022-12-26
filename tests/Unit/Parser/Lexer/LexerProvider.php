<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser\Lexer;

use HypnoTox\Toml\Parser\Token\Token;
use HypnoTox\Toml\Parser\Token\TokenType;

final class LexerProvider
{
    public function provide(): array
    {
        return [
            ...$this->provideComments(),
            ...$this->provideWhitespace(),
            ...$this->provideNewline(),
            ...$this->provideMixed(),
            ...$this->provideSimpleKeyValue(),
        ];
    }

    private function provideComments(): array
    {
        return [
            [
                '# TEST',
                [],
            ],
            [
                '# TEST # Foo',
                [],
            ],
        ];
    }

    private function provideWhitespace(): array
    {
        return [
            [
                ' # TEST',
                [],
            ],
        ];
    }

    private function provideNewline(): array
    {
        return [
            [
                "\n\r\n",
                [
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n",
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n",
                        2,
                        1,
                    ),
                ],
            ],
        ];
    }

    private function provideMixed(): array
    {
        return [
            // Comment & Newline
            [
                "# TEST\n\r\n",
                [
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n",
                        1,
                        7,
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n",
                        2,
                        1,
                    ),
                ],
            ],
            [
                "# TEST\n\r\n# test\n",
                [
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n",
                        1,
                        7,
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n",
                        2,
                        1,
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n",
                        3,
                        7,
                    ),
                ],
            ],
        ];
    }

    private function provideSimpleKeyValue(): array
    {
        return [
            [
                'foo = 1',
                [
                    new Token(
                        TokenType::T_KEY,
                        'foo',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        5,
                    ),
                    new Token(
                        TokenType::T_INTEGER,
                        1,
                        1,
                        7,
                    ),
                ],
            ],
            [
                'foo = 1.1',
                [
                    new Token(
                        TokenType::T_KEY,
                        'foo',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        5,
                    ),
                    new Token(
                        TokenType::T_FLOAT,
                        1.1,
                        1,
                        7,
                    ),
                ],
            ],
            [
                'foo = bar',
                [
                    new Token(
                        TokenType::T_KEY,
                        'foo',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        5,
                    ),
                    new Token(
                        TokenType::T_BASIC_STRING,
                        'bar',
                        1,
                        7,
                    ),
                ],
            ],
            [
                '1 = bar',
                [
                    new Token(
                        TokenType::T_KEY,
                        1,
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        3,
                    ),
                    new Token(
                        TokenType::T_BASIC_STRING,
                        'bar',
                        1,
                        5,
                    ),
                ],
            ],
            [
                '"something" = bar',
                [
                    new Token(
                        TokenType::T_QUOTED_KEY,
                        '"something"',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        13,
                    ),
                    new Token(
                        TokenType::T_BASIC_STRING,
                        'bar',
                        1,
                        15,
                    ),
                ],
            ],
            [
                '1.1 = 1.1',
                [
                    new Token(
                        TokenType::T_KEY,
                        '1',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_DOT,
                        '.',
                        1,
                        2,
                    ),
                    new Token(
                        TokenType::T_KEY,
                        '1',
                        1,
                        3,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        5,
                    ),
                    new Token(
                        TokenType::T_FLOAT,
                        '1.1',
                        1,
                        7,
                    ),
                ],
            ],
            [
                '1."1" = 1.1',
                [
                    new Token(
                        TokenType::T_KEY,
                        '1',
                        1,
                        1,
                    ),
                    new Token(
                        TokenType::T_DOT,
                        '.',
                        1,
                        2,
                    ),
                    new Token(
                        TokenType::T_QUOTED_KEY,
                        '"1"',
                        1,
                        3,
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '=',
                        1,
                        7,
                    ),
                    new Token(
                        TokenType::T_FLOAT,
                        '1.1',
                        1,
                        9,
                    ),
                ],
            ],
        ];
    }
}
