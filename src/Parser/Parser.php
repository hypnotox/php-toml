<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Exception\TomlException;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\TomlFactoryInterface;
use HypnoTox\Toml\TomlInterface;

final class Parser implements ParserInterface
{
    private const NEWLINE_TOKEN = [
        TokenType::T_RETURN,
        ...self::KEY_TOKEN,
    ];

    private const KEY_TOKEN = [
        TokenType::T_STRING,
        TokenType::T_INTEGER,
        TokenType::T_FLOAT,
        TokenType::T_BRACKET_OPEN,
    ];

    private const VALUE_TOKEN = [
        TokenType::T_STRING,
        TokenType::T_INTEGER,
        TokenType::T_FLOAT,
        TokenType::T_BRACKET_OPEN,
        TokenType::T_BOOLEAN,
        TokenType::T_DATETIME,
        TokenType::T_DATE,
        TokenType::T_TIME,
    ];

    public function __construct(
        private LexerInterface $lexer,
        private TomlFactoryInterface $tomlFactory,
    ) {
    }

    /**
     * @throws TomlException
     */
    public function parse(string $input): TomlInterface
    {
        if (!mb_check_encoding($input, 'UTF-8')) {
            throw new TomlException('TOML must be UTF-8.');
        }

        $input = str_replace("\r\n", "\n", $input);
        $stream = $this->lexer->tokenize($input);
        $toml = $this->tomlFactory->make();
        $lastPointer = $stream->getPointer();

        while (!$stream->isEOF()) {
            $token = $stream->peek();

            if (!in_array($token->getType(), self::NEWLINE_TOKEN, true)) {
                $this->raiseUnexpectedTokenException($token, self::NEWLINE_TOKEN);
            }

            if ($token->getType() === TokenType::T_RETURN) {
                $stream->consume();
                continue;
            }

            if (in_array($token->getType(), self::KEY_TOKEN, true)) {
                $toml = $this->parseKeyValuePair($stream, $toml);
                continue;
            }

            if ($stream->getPointer() === $lastPointer) {
                throw new TomlException(
                    sprintf(
                        'SyntaxError: %s on line %d:%d',
                        'Could not parse input',
                        $token->getLine(),
                        $token->getOffset() + 1,
                    ),
                );
            }
        }

        return $toml;
    }

    private function parseKeyValuePair(TokenStreamInterface $stream, TomlInterface $toml): TomlInterface
    {
        $key = $stream->consume();
        $equals = $stream->consume();

        if ($equals->getType() !== TokenType::T_EQUALS) {
            $this->raiseUnexpectedTokenException($equals, [TokenType::T_EQUALS]);
        }

        // TODO: Validate and consume value tokens.
        // TODO: Add key value pair to TOML object.

        return $toml;
    }

    /**
     * @param list<TokenType> $expected
     *
     * @throws TomlException
     */
    private function raiseUnexpectedTokenException(TokenInterface $actual, array $expected): never
    {
        throw new TomlException(
            sprintf(
                'SyntaxError: Unexpected %s on line %d:%d, expected %s%s.',
                $actual->getType()->name,
                $actual->getLine(),
                $actual->getOffset() + 1,
                count($expected) > 1 ? 'one of ' : '',
                implode(', ', array_map(static fn (TokenType $tokenType) => $tokenType->name, $expected)),
            ),
        );
    }
}
