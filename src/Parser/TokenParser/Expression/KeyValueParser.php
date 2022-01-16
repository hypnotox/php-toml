<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\TokenParser\Expression;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Stream\TokenStreamInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenInterface;
use HypnoTox\Toml\Lexer\Tokenizer\Token\TokenType;
use HypnoTox\Toml\Parser\ParserInterface;
use HypnoTox\Toml\Parser\TokenParser\AbstractTokenParser;
use HypnoTox\Toml\Parser\TokenParser\Value\BasicStringParser;
use HypnoTox\Toml\Parser\TokenParser\Value\LiteralStringParser;
use HypnoTox\Toml\Parser\TokenParser\Value\ValueParserInterface;

final class KeyValueParser extends AbstractTokenParser implements ExpressionParserInterface
{
    /**
     * @var ValueParserInterface[]
     */
    private readonly array $valueParser;

    /**
     * @param ValueParserInterface[] $valueParser
     */
    public function __construct(
        array $valueParser = null,
    ) {
        if ($valueParser) {
            $this->valueParser = $valueParser;
        } else {
            $this->valueParser = [
                new BasicStringParser(),
                new LiteralStringParser(),
            ];
        }
    }

    public function canHandle(TokenInterface $token): bool
    {
        return TokenType::T_KEY === $token->getType();
    }

    public function parse(TomlBuilderInterface $builder, TokenStreamInterface $stream): void
    {
        $key = $stream->consume();
        $equals = $stream->consume();

        $this->assertToken($equals, [TokenType::T_EQUALS]);
        $this->assertToken($stream->peek(), ParserInterface::VALUE_TOKEN);

        $valueToken = $stream->peek();
        $value = null;

        foreach ($this->valueParser as $parser) {
            if ($parser->canHandle($valueToken)) {
                /** @var mixed */
                $value = $parser->parse($stream);
            }
        }

        $builder->set($key->getValue(), $value);
    }
}
