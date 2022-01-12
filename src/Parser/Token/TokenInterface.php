<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Token;

interface TokenInterface extends \Stringable
{
    public function __construct(TokenType $type, string $value, int $line, int $offset);

    public function getType(): TokenType;

    public function getValue(): string;

    public function getLine(): int;

    public function getOffset(): int;
}
