<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Lexer\TokenLexer\CommentLexer;
use HypnoTox\Toml\Lexer\TokenLexer\NewlineLexer;
use HypnoTox\Toml\Lexer\TokenLexer\TokenLexerInterface;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Token\TokenInterface;

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
        $tokenLexer = $this->getTokenLexer();

        while (!$stream->isEndOfFile()) {
            foreach ($tokenLexer as $lexer) {
                if ($lexer->canTokenize($stream)) {
                    $tokens = [...$tokens, ...$lexer->tokenize($stream)];
                    continue 2;
                }
            }

            throw new UnableToParseInputException();
        }

        return $tokens;
    }

    /**
     * @return TokenLexerInterface[]
     */
    private function getTokenLexer(): array
    {
        return [
            new CommentLexer(),
            new NewlineLexer(),
        ];
    }
}
