<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Token\Token;
use HypnoTox\Toml\Token\TokenInterface;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
final class Lexer implements LexerInterface
{
    /**
     * @throws EncodingException|UnableToParseInputException
     */
    public function tokenize(string|Stream $input): array
    {
        /** @var TokenInterface[] $tokens */
        $tokens = [];
        $stream = $input instanceof Stream ? $input : new Stream($input);
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
                $unableToTokenize = mb_substr($unableToTokenize, 0, 100) . '[...]';
            }

            throw new UnableToParseInputException("Unable to tokenize: '$unableToTokenize'");
        }

        return $tokens;
    }
}
