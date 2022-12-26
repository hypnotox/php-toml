<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Lexer;

use HypnoTox\Toml\Exception\EncodingException;
use HypnoTox\Toml\Exception\UnableToParseInputException;
use HypnoTox\Toml\Lexer\TokenLexer\CommentLexer;
use HypnoTox\Toml\Lexer\TokenLexer\EqualsLexer;
use HypnoTox\Toml\Lexer\TokenLexer\FloatLexer;
use HypnoTox\Toml\Lexer\TokenLexer\IntegerLexer;
use HypnoTox\Toml\Lexer\TokenLexer\NewlineLexer;
use HypnoTox\Toml\Lexer\TokenLexer\StringLexer;
use HypnoTox\Toml\Lexer\TokenLexer\TokenLexerInterface;
use HypnoTox\Toml\Lexer\TokenLexer\WhitespaceLexer;
use HypnoTox\Toml\Stream\Stream;
use HypnoTox\Toml\Token\TokenInterface;
use HypnoTox\Toml\Token\TokenType;

/**
 * @internal
 */
final class Lexer implements LexerInterface
{
    /**
     * @var TokenLexerInterface[]
     */
    private readonly array $tokenLexer;

    /**
     * @param TokenLexerInterface[]|null $tokenLexer
     */
    public function __construct(
        array $tokenLexer = null,
    ) {
        $this->tokenLexer = $tokenLexer ?? [
            new CommentLexer(),
            new NewlineLexer(),
            new WhitespaceLexer(),
            new EqualsLexer(),
            new IntegerLexer(),
            new FloatLexer(),
            new StringLexer(),
        ];
    }

    /**
     * @throws EncodingException|UnableToParseInputException
     */
    public function tokenize(string|Stream $input): array
    {
        /** @var TokenInterface[] $tokens */
        $tokens = [];
        $stream = $input instanceof Stream ? $input : new Stream($input);

        while (!$stream->isEndOfFile()) {
            foreach ($this->tokenLexer as $lexer) {
                if ($lexer->canTokenize($stream)) {
                    $tokens = [...$tokens, ...$lexer->tokenize($stream)];
                    continue 2;
                }
            }

            $unableToTokenize = $stream->consumeUntil(TokenType::T_EOF);

            if (mb_strlen($unableToTokenize) > 100) {
                $unableToTokenize = mb_substr($unableToTokenize, 0, 100) . '[...]';
            }

            throw new UnableToParseInputException("Unable to tokenize: '$unableToTokenize'");
        }

        return $tokens;
    }
}
