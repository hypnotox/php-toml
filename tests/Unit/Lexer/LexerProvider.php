<?php

namespace HypnoTox\Toml\Tests\Unit\Lexer;

use HypnoTox\Toml\Token\Token;
use HypnoTox\Toml\Token\TokenType;

class LexerProvider
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
                [
                    new Token(
                        TokenType::T_COMMENT,
                        '# TEST'
                    ),
                ],
            ],
            [
                '# TEST # Foo',
                [
                    new Token(
                        TokenType::T_COMMENT,
                        '# TEST # Foo'
                    ),
                ],
            ],
        ];
    }

    private function provideWhitespace(): array
    {
        return [
            [
                ' # TEST',
                [
                    new Token(
                        TokenType::T_COMMENT,
                        '# TEST'
                    ),
                ],
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
                        "\n"
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n"
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
                        TokenType::T_COMMENT,
                        '# TEST'
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n"
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n"
                    ),
                ],
            ],
            [
                "# TEST\n\r\n# test\n",
                [
                    new Token(
                        TokenType::T_COMMENT,
                        '# TEST'
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n"
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\r\n"
                    ),
                    new Token(
                        TokenType::T_COMMENT,
                        '# test'
                    ),
                    new Token(
                        TokenType::T_NEWLINE,
                        "\n"
                    ),
                ],
            ],
        ];
    }

    private function provideSimpleKeyValue(): array
    {
        return [
            [
                "foo = 1",
                [
                    new Token(
                        TokenType::T_STRING,
                        'foo'
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '='
                    ),
                    new Token(
                        TokenType::T_INTEGER,
                        1
                    ),
                ],
            ],
            [
                "foo = 1.1",
                [
                    new Token(
                        TokenType::T_STRING,
                        'foo'
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '='
                    ),
                    new Token(
                        TokenType::T_FLOAT,
                        1.1
                    ),
                ],
            ],
            [
                "foo = bar",
                [
                    new Token(
                        TokenType::T_STRING,
                        'foo'
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '='
                    ),
                    new Token(
                        TokenType::T_STRING,
                        'bar'
                    ),
                ],
            ],
            [
                "1 = bar",
                [
                    new Token(
                        TokenType::T_INTEGER,
                        1
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '='
                    ),
                    new Token(
                        TokenType::T_STRING,
                        'bar'
                    ),
                ],
            ],
            [
                "1.1 = bar",
                [
                    new Token(
                        TokenType::T_FLOAT,
                        1.1
                    ),
                    new Token(
                        TokenType::T_EQUALS,
                        '='
                    ),
                    new Token(
                        TokenType::T_STRING,
                        'bar'
                    ),
                ],
            ],
        ];
    }
}