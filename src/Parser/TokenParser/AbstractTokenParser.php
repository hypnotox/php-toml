<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser;

use HypnoTox\Toml\Parser\Exception\UnexpectedTokenException;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

abstract class AbstractTokenParser implements TokenParserInterface
{
    /**
     * @param list<TokenType> $expected
     */
    protected function isTokenExpected(TokenInterface $token, array $expected): bool
    {
        return in_array($token->getType(), $expected, true);
    }

    /**
     * @param list<TokenType> $expected
     *
     * @throws UnexpectedTokenException
     */
    protected function assertToken(TokenInterface $token, array $expected): void
    {
        if (!$this->isTokenExpected($token, $expected)) {
            $this->raiseUnexpectedTokenException($token, $expected);
        }
    }

    /**
     * @param list<TokenType> $expected
     *
     * @throws UnexpectedTokenException
     */
    protected function raiseUnexpectedTokenException(TokenInterface $actual, array $expected): never
    {
        throw new UnexpectedTokenException(
            sprintf(
                'SyntaxError: Unexpected %s on line %d:%d, expected %s%s.',
                $actual->getType()->name,
                $actual->getLine(),
                $actual->getOffset() + 1,
                count($expected) > 1 ? 'one of ' : '',
                implode(', ', array_map(static fn(TokenType $tokenType) => $tokenType->name, $expected)),
            ),
        );
    }
}