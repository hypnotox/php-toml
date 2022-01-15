<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Value;

use HypnoTox\Toml\Parser\Stream\TokenStreamInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

class LiteralStringParser implements ValueParserInterface
{
    public function canHandle(TokenInterface $token): bool
    {
        return $token->getType() === TokenType::T_LITERAL_STRING;
    }

    public function parse(TokenStreamInterface $stream): string
    {
        return $stream->consume()->getValue();
    }
}