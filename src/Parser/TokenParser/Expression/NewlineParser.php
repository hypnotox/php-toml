<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Expression;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Parser\ParserInterface;
use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\Parser\TokenParser\AbstractTokenParser;
use HypnoTox\Toml\Parser\TokenParser\Value\LiteralStringParser;
use HypnoTox\Toml\Parser\TokenParser\Value\ValueParserInterface;

class NewlineParser extends AbstractTokenParser implements ExpressionParserInterface
{
    public function canHandle(TokenInterface $token): bool
    {
        return $token->getType() === TokenType::T_RETURN;
    }

    public function parse(TomlBuilderInterface $builder, TokenStreamInterface $stream): void
    {
        $stream->consume();
    }
}