<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Expression;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Lexer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Token\TokenInterface;
use HypnoTox\Toml\Lexer\Token\TokenType;
use HypnoTox\Toml\Parser\TokenParser\AbstractTokenParser;

final class NewlineParser extends AbstractTokenParser implements ExpressionParserInterface
{
    public function canHandle(TokenInterface $token): bool
    {
        return TokenType::T_RETURN === $token->getType();
    }

    public function parse(TomlBuilderInterface $builder, TokenStreamInterface $stream): void
    {
        $stream->consume();
    }
}
