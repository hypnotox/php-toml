<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Token\Token;
use HypnoTox\Toml\Parser\Token\TokenInterface;
use HypnoTox\Toml\Parser\Token\TokenType;

/**
 * @internal
 */
final class Lexer implements LexerInterface
{
    /**
     * @throws EncodingException|UnableToParseInputException
     */
    public function tokenize(string|StringStream $input): array
    {
        /** @var TokenInterface[] $tokens */
        $tokens = [];
        $stream = $input instanceof StringStream ? $input : new StringStream($input);
        $expectedTokens = TokenType::getDefaultTokens();

        while (!$stream->isEndOfFile()) {
            $stream->consumeMatching('([ \t]+)');

            foreach ($expectedTokens as $tokenType) {
                if ($tokenType->matches($stream)) {
                    $tokens[] = new Token($tokenType, $stream->consumeMatching($tokenType));
                    $expectedTokens = $tokenType->getExpectedTokens();

                    continue 2;
                }
            }

            $unableToTokenize = $stream->consumeMatching('(.*)');

            if (mb_strlen($unableToTokenize) > 100) {
                $unableToTokenize = mb_substr($unableToTokenize, 0, 100).'[...]';
            }

            throw new UnableToParseInputException("Unable to tokenize: '$unableToTokenize'");
        }

        return $tokens;
    }
}
