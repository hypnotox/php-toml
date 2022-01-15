<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Builder\TomlBuilderInterface;
use HypnoTox\Toml\Parser\Exception\EncodingException;
use HypnoTox\Toml\Parser\Exception\TomlExceptionInterface;
use HypnoTox\Toml\Parser\Exception\UnableToParseInputException;
use HypnoTox\Toml\Parser\TokenParser\Expression\ExpressionParserInterface;
use HypnoTox\Toml\Parser\TokenParser\Expression\KeyValueParser;
use HypnoTox\Toml\Parser\TokenParser\Expression\NewlineParser;
use HypnoTox\Toml\TomlInterface;

final class Parser implements ParserInterface
{
    /**
     * @var ExpressionParserInterface[]
     */
    private readonly array $expressionParser;

    /**
     * @param ExpressionParserInterface[] $expressionParser
     */
    public function __construct(
        private LexerInterface $lexer,
        private TomlBuilderInterface $tomlBuilder,
        array $expressionParser = null,
    ) {
        if ($expressionParser) {
            $this->expressionParser = $expressionParser;
        } else {
            $this->expressionParser = [
                new NewlineParser(),
                new KeyValueParser(),
            ];
        }
    }

    /**
     * @throws UnableToParseInputException
     * @throws EncodingException
     * @throws TomlExceptionInterface
     */
    public function parse(string $input): TomlInterface
    {
        if (!mb_check_encoding($input, 'UTF-8')) {
            throw new EncodingException('TOML must be UTF-8.');
        }

        $input = str_replace("\r\n", "\n", $input);
        $stream = $this->lexer->tokenize($input);
        $lastPointer = $stream->getPointer();

        while (!$stream->isEOF()) {
            $stream->consumeNewlines();
            $token = $stream->peek();

            foreach ($this->expressionParser as $parser) {
                if ($parser->canHandle($token)) {
                    $parser->parse($this->tomlBuilder, $stream);
                }
            }

            if ($stream->getPointer() === $lastPointer) {
                throw new UnableToParseInputException(
                    sprintf(
                        'SyntaxError: Could not parse input on line %d:%d: "%s"',
                        $token->getLine(),
                        $token->getOffset() + 1,
                        $token->getValue(),
                    ),
                );
            }
        }

        return $this->tomlBuilder->build();
    }
}
