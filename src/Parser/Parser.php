<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Parser\Lexer\Lexer;
use HypnoTox\Toml\Parser\Lexer\LexerInterface;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\TomlFactory;
use HypnoTox\Toml\TomlFactoryInterface;
use HypnoTox\Toml\TomlInterface;

/**
 * @internal
 */
final class Parser implements ParserInterface
{
    private readonly LexerInterface $lexer;
    private readonly TomlFactoryInterface $factory;

    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->factory = new TomlFactory();
    }

    public function parse(string $input): TomlInterface
    {
        $tokens = $this->lexer->tokenize($input);
        $data = $this->parseTokens($tokens);

        return $this->factory->make($data);
    }

    /**
     * @param TokenInterface[] $tokens
     */
    private function parseTokens(array $tokens): array
    {
        $data = [];

        foreach ($tokens as $token) {
            // Do stuff
            $data[] = $token;
        }

        return $data;
    }
}
