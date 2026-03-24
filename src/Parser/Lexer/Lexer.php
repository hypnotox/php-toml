<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser\Lexer;

use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Stream\StringStream;
use HypnoTox\Toml\Parser\Stream\StringStreamInterface;
use HypnoTox\Toml\Parser\Token\Token;
use HypnoTox\Toml\Parser\Token\TokenType;
use Override;

/**
 * @internal
 */
final class Lexer implements LexerInterface
{
    private StringStreamInterface $stream;

    public function __construct(StringStreamInterface|string $input)
    {
        $this->stream = \is_string($input) ? new StringStream($input) : $input;
    }

    #[Override]
    public function next(LexerContext $context): Token
    {
        $token = $this->scanToken($context);

        return $token;
    }

    #[Override]
    public function peek(LexerContext $context): Token
    {
        $state = $this->stream->save();
        $token = $this->scanToken($context);
        $this->stream->restore($state);

        return $token;
    }

    /** @psalm-api */
    public function getStream(): StringStreamInterface
    {
        return $this->stream;
    }

    private function scanToken(LexerContext $context): Token
    {
        $line = $this->stream->getLine();
        $col = $this->stream->getColumn();

        if ($this->stream->isEndOfFile()) {
            return new Token(TokenType::T_EOF, '', $line, $col);
        }

        return match ($context) {
            LexerContext::LineStart => $this->scanLineStart($line, $col),
            LexerContext::Key => $this->scanKey($line, $col),
            LexerContext::AfterKey => $this->scanAfterKey($line, $col),
            LexerContext::Value,
            LexerContext::ArrayItem => $this->scanValue($context, $line, $col),
            LexerContext::InlineTable => $this->scanInlineTable($line, $col),
            LexerContext::InlineTableAfterValue => $this->scanInlineTableAfterValue($line, $col),
        };
    }

    private function scanLineStart(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        // Check for bare CR (invalid in TOML)
        if ("\r" === $char) {
            if ($this->stream->isEndOfFile() || !$this->stream->matches('\r\n')) {
                throw new SyntaxException("Bare carriage return (CR) at line {$line}, column {$col}");
            }
        }

        return match (true) {
            '[' === $char => $this->scanBracket($line, $col),
            '#' === $char => $this->scanComment($line, $col),
            $this->isNewline($char) => $this->scanNewline($line, $col),
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            default => $this->scanKey($line, $col),
        };
    }

    private function scanKey(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        return match (true) {
            '"' === $char => $this->scanBasicString($line, $col),
            "'" === $char => $this->scanLiteralString($line, $col),
            $this->isBareKeyChar($char) => $this->scanBareKey($line, $col),
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            default => throw new SyntaxException("Unexpected character '{$char}' at line {$line}, column {$col}, expected key"),
        };
    }

    private function scanAfterKey(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        if (']' === $char) {
            $this->stream->consume();
            if (!$this->stream->isEndOfFile() && ']' === $this->stream->peek()) {
                $this->stream->consume();

                return new Token(TokenType::T_DOUBLE_RIGHT_BRACKET, ']]', $line, $col);
            }

            return new Token(TokenType::T_RIGHT_BRACKET, ']', $line, $col);
        }

        return match (true) {
            '=' === $char => new Token(TokenType::T_EQUALS, $this->stream->consume(), $line, $col),
            '.' === $char => new Token(TokenType::T_DOT, $this->stream->consume(), $line, $col),
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            default => throw new SyntaxException("Unexpected character '{$char}' at line {$line}, column {$col}, expected '=' or '.'"),
        };
    }

    private function scanValue(LexerContext $context, int $line, int $col): Token
    {
        $char = $this->stream->peek();

        // Handle array-specific tokens
        if (LexerContext::ArrayItem === $context) {
            if (']' === $char) {
                return new Token(TokenType::T_RIGHT_BRACKET, $this->stream->consume(), $line, $col);
            }
            if (',' === $char) {
                return new Token(TokenType::T_COMMA, $this->stream->consume(), $line, $col);
            }
            if ($this->isNewline($char)) {
                return $this->scanNewline($line, $col);
            }
            if ('#' === $char) {
                return $this->scanComment($line, $col);
            }
        }

        return match (true) {
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            '"' === $char => $this->scanStringValue($line, $col),
            "'" === $char => $this->scanLiteralStringValue($line, $col),
            '[' === $char => new Token(TokenType::T_LEFT_BRACKET, $this->stream->consume(), $line, $col),
            '{' === $char => new Token(TokenType::T_LEFT_BRACE, $this->stream->consume(), $line, $col),
            't' === $char, 'f' === $char => $this->scanBool($line, $col),
            'i' === $char, 'n' === $char => $this->scanSpecialFloat($line, $col),
            '+' === $char, '-' === $char => $this->scanSignedValue($line, $col),
            $this->isDigit($char) => $this->scanNumberOrDatetime($line, $col),
            default => throw new SyntaxException("Unexpected character '{$char}' at line {$line}, column {$col}, expected value"),
        };
    }

    private function scanInlineTable(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        return match (true) {
            '}' === $char => new Token(TokenType::T_RIGHT_BRACE, $this->stream->consume(), $line, $col),
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            default => $this->scanKey($line, $col),
        };
    }

    private function scanInlineTableAfterValue(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        return match (true) {
            ',' === $char => new Token(TokenType::T_COMMA, $this->stream->consume(), $line, $col),
            '}' === $char => new Token(TokenType::T_RIGHT_BRACE, $this->stream->consume(), $line, $col),
            $this->isWhitespace($char) => $this->scanWhitespace($line, $col),
            default => throw new SyntaxException("Unexpected character '{$char}' at line {$line}, column {$col}, expected ',' or '}'"),
        };
    }

    private function scanBracket(int $line, int $col): Token
    {
        $this->stream->consume(); // consume first [

        if (!$this->stream->isEndOfFile() && '[' === $this->stream->peek()) {
            $this->stream->consume(); // consume second [

            return new Token(TokenType::T_DOUBLE_LEFT_BRACKET, '[[', $line, $col);
        }

        return new Token(TokenType::T_LEFT_BRACKET, '[', $line, $col);
    }

    private function scanBareKey(int $line, int $col): Token
    {
        $value = $this->stream->consumeMatching('[A-Za-z0-9_\-]+');

        return new Token(TokenType::T_BARE_KEY, $value, $line, $col);
    }

    private function scanComment(int $line, int $col): Token
    {
        $this->stream->consume(); // consume #
        $value = '#';

        while (!$this->stream->isEndOfFile()) {
            $char = $this->stream->peek();

            if ("\n" === $char || "\r" === $char) {
                break;
            }

            $ord = mb_ord($char);

            // Reject control characters in comments (U+0000-U+0008, U+000A-U+001F, U+007F)
            // Tab (U+0009) is allowed
            if (false !== $ord) {
                if ($ord <= 0x08 || ($ord >= 0x0A && $ord <= 0x1F) || 0x7F === $ord) {
                    throw new SyntaxException('Control character U+'.\sprintf('%04X', $ord)." in comment at line {$line}, column {$col}");
                }
            }

            $value .= $this->stream->consume();
        }

        return new Token(TokenType::T_COMMENT, $value, $line, $col);
    }

    private function scanNewline(int $line, int $col): Token
    {
        $char = $this->stream->peek();

        if ("\r" === $char) {
            $this->stream->consume(); // consume \r
            if ($this->stream->isEndOfFile() || "\n" !== $this->stream->peek()) {
                throw new SyntaxException("Bare carriage return (CR) at line {$line}, column {$col}");
            }
            $this->stream->consume(); // consume \n

            return new Token(TokenType::T_NEWLINE, "\r\n", $line, $col);
        }

        return new Token(TokenType::T_NEWLINE, $this->stream->consume(), $line, $col);
    }

    private function scanWhitespace(int $line, int $col): Token
    {
        $value = $this->stream->consumeMatching('[ \t]+');

        return new Token(TokenType::T_WHITESPACE, $value, $line, $col);
    }

    private function scanStringValue(int $line, int $col): Token
    {
        // Check for multiline: """
        if ('"""' === $this->stream->peek(3)) {
            return $this->scanMultilineBasicString($line, $col);
        }

        return $this->scanBasicString($line, $col);
    }

    private function scanLiteralStringValue(int $line, int $col): Token
    {
        // Check for multiline: '''
        if ("'''" === $this->stream->peek(3)) {
            return $this->scanMultilineLiteralString($line, $col);
        }

        return $this->scanLiteralString($line, $col);
    }

    private function scanBasicString(int $line, int $col): Token
    {
        $this->stream->consume(); // opening "
        $value = '';

        while (!$this->stream->isEndOfFile()) {
            $char = $this->stream->peek();

            if ('"' === $char) {
                $this->stream->consume(); // closing "

                return new Token(TokenType::T_BASIC_STRING, $value, $line, $col);
            }

            if ('\\' === $char) {
                $value .= $this->scanEscapeSequence();

                continue;
            }

            if ($this->isNewline($char)) {
                throw new SyntaxException("Newline in basic string at line {$line}, column {$col}");
            }

            $ord = mb_ord($char);
            if (false !== $ord && (($ord <= 0x1F && 0x09 !== $ord) || 0x7F === $ord)) {
                throw new SyntaxException('Control character U+'.\sprintf('%04X', $ord)." in basic string at line {$line}, column {$col}");
            }

            $value .= $this->stream->consume();
        }

        throw new SyntaxException("Unterminated basic string at line {$line}, column {$col}");
    }

    private function scanMultilineBasicString(int $line, int $col): Token
    {
        $this->stream->consume(3); // opening """
        $value = '';

        // Trim first newline immediately after opening delimiter
        if (!$this->stream->isEndOfFile() && $this->isNewline($this->stream->peek())) {
            if ("\r" === $this->stream->peek()) {
                $this->stream->consume(); // consume \r
                if (!$this->stream->isEndOfFile() && "\n" === $this->stream->peek()) {
                    $this->stream->consume(); // consume \n
                }
            } else {
                $this->stream->consume(); // consume \n
            }
        }

        while (!$this->stream->isEndOfFile()) {
            $char = $this->stream->peek();

            if ('"' === $char) {
                // Check for closing """ (with up to 2 extra quotes as content)
                $quotes = '';
                while (!$this->stream->isEndOfFile() && '"' === $this->stream->peek()) {
                    $quotes .= $this->stream->consume();
                }

                if (\strlen($quotes) >= 3) {
                    $extraQuotes = \strlen($quotes) - 3;
                    // Max 2 extra quotes (5 total) before closing delimiter
                    if ($extraQuotes > 2) {
                        throw new SyntaxException("Too many quotes in multiline basic string at line {$line}, column {$col}");
                    }
                    $value .= str_repeat('"', $extraQuotes);

                    return new Token(TokenType::T_ML_BASIC_STRING, $value, $line, $col);
                }

                // Not enough quotes for closing, they're content
                $value .= $quotes;

                continue;
            }

            if ('\\' === $char) {
                $this->stream->consume(); // consume backslash

                // Line ending backslash: trim whitespace, then newline and following whitespace/newlines
                if (!$this->stream->isEndOfFile()) {
                    // Check for optional whitespace followed by newline
                    $state = $this->stream->save();
                    $this->stream->consumeMatching('[ \t]*');

                    if (!$this->stream->isEndOfFile() && $this->isNewline($this->stream->peek())) {
                        // Consume the newline
                        if ("\r" === $this->stream->peek()) {
                            $this->stream->consume();
                            if (!$this->stream->isEndOfFile() && "\n" === $this->stream->peek()) {
                                $this->stream->consume();
                            }
                        } else {
                            $this->stream->consume();
                        }
                        // Consume all following whitespace and newlines
                        $this->stream->consumeMatching('[ \t\r\n]*');

                        continue;
                    }

                    // Not a line ending backslash, restore and process as escape
                    $this->stream->restore($state);
                }

                // Regular escape
                $value .= $this->processEscapeChar();

                continue;
            }

            // Check for bare CR
            if ("\r" === $char) {
                $this->stream->consume();
                if ($this->stream->isEndOfFile() || "\n" !== $this->stream->peek()) {
                    throw new SyntaxException("Bare carriage return (CR) in multiline basic string at line {$line}, column {$col}");
                }
                $value .= "\r".$this->stream->consume(); // \r\n -> add both, normalizing happens elsewhere

                continue;
            }

            $ord = mb_ord($char);
            if (false !== $ord && (($ord <= 0x1F && 0x09 !== $ord && 0x0A !== $ord && 0x0D !== $ord) || 0x7F === $ord)) {
                throw new SyntaxException('Control character U+'.\sprintf('%04X', $ord)." in multiline basic string at line {$line}, column {$col}");
            }

            $value .= $this->stream->consume();
        }

        throw new SyntaxException("Unterminated multiline basic string at line {$line}, column {$col}");
    }

    private function scanLiteralString(int $line, int $col): Token
    {
        $this->stream->consume(); // opening '
        $value = '';

        while (!$this->stream->isEndOfFile()) {
            $char = $this->stream->peek();

            if ("'" === $char) {
                $this->stream->consume(); // closing '

                return new Token(TokenType::T_LITERAL_STRING, $value, $line, $col);
            }

            if ($this->isNewline($char)) {
                throw new SyntaxException("Newline in literal string at line {$line}, column {$col}");
            }

            $ord = mb_ord($char);
            if (false !== $ord && (($ord <= 0x1F && 0x09 !== $ord) || 0x7F === $ord)) {
                throw new SyntaxException("Control character in literal string at line {$line}, column {$col}");
            }

            $value .= $this->stream->consume();
        }

        throw new SyntaxException("Unterminated literal string at line {$line}, column {$col}");
    }

    private function scanMultilineLiteralString(int $line, int $col): Token
    {
        $this->stream->consume(3); // opening '''
        $value = '';

        // Trim first newline immediately after opening delimiter
        if (!$this->stream->isEndOfFile() && $this->isNewline($this->stream->peek())) {
            if ("\r" === $this->stream->peek()) {
                $this->stream->consume();
                if (!$this->stream->isEndOfFile() && "\n" === $this->stream->peek()) {
                    $this->stream->consume();
                }
            } else {
                $this->stream->consume();
            }
        }

        while (!$this->stream->isEndOfFile()) {
            $char = $this->stream->peek();

            if ("'" === $char) {
                $quotes = '';
                while (!$this->stream->isEndOfFile() && "'" === $this->stream->peek()) {
                    $quotes .= $this->stream->consume();
                }

                if (\strlen($quotes) >= 3) {
                    $extraQuotes = \strlen($quotes) - 3;
                    if ($extraQuotes > 2) {
                        throw new SyntaxException("Too many quotes in multiline literal string at line {$line}, column {$col}");
                    }
                    $value .= str_repeat("'", $extraQuotes);

                    return new Token(TokenType::T_ML_LITERAL_STRING, $value, $line, $col);
                }

                $value .= $quotes;

                continue;
            }

            // Check for bare CR
            if ("\r" === $char) {
                $this->stream->consume();
                if ($this->stream->isEndOfFile() || "\n" !== $this->stream->peek()) {
                    throw new SyntaxException("Bare carriage return (CR) in multiline literal string at line {$line}, column {$col}");
                }
                $value .= "\r".$this->stream->consume();

                continue;
            }

            $ord = mb_ord($char);
            if (false !== $ord && (($ord <= 0x1F && 0x09 !== $ord && 0x0A !== $ord && 0x0D !== $ord) || 0x7F === $ord)) {
                throw new SyntaxException("Control character in multiline literal string at line {$line}, column {$col}");
            }

            $value .= $this->stream->consume();
        }

        throw new SyntaxException("Unterminated multiline literal string at line {$line}, column {$col}");
    }

    private function scanEscapeSequence(): string
    {
        $this->stream->consume(); // consume backslash

        return $this->processEscapeChar();
    }

    private function processEscapeChar(): string
    {
        if ($this->stream->isEndOfFile()) {
            throw new SyntaxException('Unexpected end of file in escape sequence');
        }

        $char = $this->stream->consume();

        return match ($char) {
            'b' => "\x08",
            't' => "\t",
            'n' => "\n",
            'f' => "\x0C",
            'r' => "\r",
            '"' => '"',
            '\\' => '\\',
            'u' => $this->scanUnicodeEscape(4),
            'U' => $this->scanUnicodeEscape(8),
            default => throw new SyntaxException("Invalid escape sequence '\\{$char}'"),
        };
    }

    private function scanUnicodeEscape(int $length): string
    {
        $hex = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($this->stream->isEndOfFile()) {
                throw new SyntaxException('Unexpected end of file in unicode escape');
            }
            $hex .= $this->stream->consume();
        }

        if (!preg_match('/^[0-9A-Fa-f]{'.$length.'}$/', $hex)) {
            throw new SyntaxException("Invalid unicode escape '\\".(4 === $length ? 'u' : 'U')."{$hex}'");
        }

        $codepoint = hexdec($hex);

        if ($codepoint > 0x10FFFF || ($codepoint >= 0xD800 && $codepoint <= 0xDFFF)) {
            throw new SyntaxException("Invalid unicode codepoint U+{$hex}");
        }

        $result = mb_chr((int) $codepoint, 'UTF-8');

        if (false === $result) {
            throw new SyntaxException("Invalid unicode codepoint U+{$hex}");
        }

        return $result;
    }

    private function scanBool(int $line, int $col): Token
    {
        if ($this->stream->matches('true(?![A-Za-z0-9_\-])')) {
            return new Token(TokenType::T_BOOL, $this->stream->consume(4), $line, $col);
        }
        if ($this->stream->matches('false(?![A-Za-z0-9_\-])')) {
            return new Token(TokenType::T_BOOL, $this->stream->consume(5), $line, $col);
        }

        // Not a bool, fall through to bare key or error depending on context
        throw new SyntaxException("Unexpected character at line {$line}, column {$col}, expected value");
    }

    private function scanSpecialFloat(int $line, int $col): Token
    {
        if ($this->stream->matches('inf(?![A-Za-z0-9_\-])')) {
            return new Token(TokenType::T_FLOAT, $this->stream->consume(3), $line, $col);
        }
        if ($this->stream->matches('nan(?![A-Za-z0-9_\-])')) {
            return new Token(TokenType::T_FLOAT, $this->stream->consume(3), $line, $col);
        }

        throw new SyntaxException("Unexpected character at line {$line}, column {$col}, expected value");
    }

    private function scanSignedValue(int $line, int $col): Token
    {
        $sign = $this->stream->peek();
        $state = $this->stream->save();
        $this->stream->consume(); // consume sign

        if ($this->stream->isEndOfFile()) {
            $this->stream->restore($state);
            throw new SyntaxException("Unexpected end of file after '{$sign}' at line {$line}, column {$col}");
        }

        $next = $this->stream->peek();

        // +inf, -inf, +nan, -nan
        if ($this->stream->matches('inf(?![A-Za-z0-9_\-])')) {
            $val = $sign.$this->stream->consume(3);

            return new Token(TokenType::T_FLOAT, $val, $line, $col);
        }
        if ($this->stream->matches('nan(?![A-Za-z0-9_\-])')) {
            $val = $sign.$this->stream->consume(3);

            return new Token(TokenType::T_FLOAT, $val, $line, $col);
        }

        if ($this->isDigit($next)) {
            $this->stream->restore($state);

            return $this->scanNumberOrDatetime($line, $col);
        }

        $this->stream->restore($state);
        throw new SyntaxException("Unexpected character '{$next}' after '{$sign}' at line {$line}, column {$col}");
    }

    private function scanNumberOrDatetime(int $line, int $col): Token
    {
        $state = $this->stream->save();

        // Try datetime first (most specific)
        $token = $this->tryDatetime($line, $col);
        if (null !== $token) {
            return $token;
        }

        $this->stream->restore($state);

        // Try number (with prefix detection)
        return $this->scanNumber($line, $col);
    }

    private function tryDatetime(int $line, int $col): ?Token
    {
        // Offset DateTime: 1979-05-27T07:32:00Z or with offset (case-insensitive t and z)
        $match = $this->stream->peekMatching('\d{4}-\d{2}-\d{2}[Tt ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[Zz]|[+-]\d{2}:\d{2})');
        if ('' !== $match) {
            $this->stream->consume(mb_strlen($match));

            return new Token(TokenType::T_OFFSET_DATETIME, $match, $line, $col);
        }

        // Local DateTime: 1979-05-27T07:32:00 (case-insensitive t)
        $match = $this->stream->peekMatching('\d{4}-\d{2}-\d{2}[Tt ]\d{2}:\d{2}:\d{2}(?:\.\d+)?');
        if ('' !== $match) {
            $this->stream->consume(mb_strlen($match));

            return new Token(TokenType::T_LOCAL_DATETIME, $match, $line, $col);
        }

        // Local Date: 1979-05-27
        $match = $this->stream->peekMatching('\d{4}-\d{2}-\d{2}');
        if ('' !== $match) {
            // Make sure it's not just the start of a negative number after a date-looking integer
            $afterMatch = mb_substr($this->stream->peek(mb_strlen($match) + 1), mb_strlen($match), 1);
            if ('' === $afterMatch || !$this->isDigit($afterMatch)) {
                $this->stream->consume(mb_strlen($match));

                return new Token(TokenType::T_LOCAL_DATE, $match, $line, $col);
            }
        }

        // Local Time: 07:32:00
        $match = $this->stream->peekMatching('\d{2}:\d{2}:\d{2}(?:\.\d+)?');
        if ('' !== $match) {
            $this->stream->consume(mb_strlen($match));

            return new Token(TokenType::T_LOCAL_TIME, $match, $line, $col);
        }

        return null;
    }

    private function scanNumber(int $line, int $col): Token
    {
        // Optional sign
        $sign = '';
        if ($this->stream->matches('[+-]')) {
            $sign = $this->stream->consume();
        }

        // Hex, octal, binary (signs not allowed per TOML spec)
        if ($this->stream->matches('0x')) {
            if ('' !== $sign) {
                throw new SyntaxException("Signs not allowed on hex integers at line {$line}, column {$col}");
            }
            $prefix = $this->stream->consume(2);
            $digits = $this->stream->consumeMatching('[0-9A-Fa-f][0-9A-Fa-f_]*');
            if ('' === $digits) {
                throw new SyntaxException("Incomplete hex integer at line {$line}, column {$col}");
            }
            $fullVal = $prefix.$digits;
            $this->validateUnderscores($fullVal, $line, $col);

            return new Token(TokenType::T_HEX_INTEGER, $fullVal, $line, $col);
        }
        if ($this->stream->matches('0o')) {
            if ('' !== $sign) {
                throw new SyntaxException("Signs not allowed on octal integers at line {$line}, column {$col}");
            }
            $prefix = $this->stream->consume(2);
            $digits = $this->stream->consumeMatching('[0-7][0-7_]*');
            if ('' === $digits) {
                throw new SyntaxException("Incomplete octal integer at line {$line}, column {$col}");
            }
            $fullVal = $prefix.$digits;
            $this->validateUnderscores($fullVal, $line, $col);

            return new Token(TokenType::T_OCT_INTEGER, $fullVal, $line, $col);
        }
        if ($this->stream->matches('0b')) {
            if ('' !== $sign) {
                throw new SyntaxException("Signs not allowed on binary integers at line {$line}, column {$col}");
            }
            $prefix = $this->stream->consume(2);
            $digits = $this->stream->consumeMatching('[01][01_]*');
            if ('' === $digits) {
                throw new SyntaxException("Incomplete binary integer at line {$line}, column {$col}");
            }
            $fullVal = $prefix.$digits;
            $this->validateUnderscores($fullVal, $line, $col);

            return new Token(TokenType::T_BIN_INTEGER, $fullVal, $line, $col);
        }

        // Decimal integer or float
        $intPart = $this->stream->consumeMatching('[0-9][0-9_]*');
        if ('' === $intPart && '' !== $sign) {
            throw new SyntaxException("Expected digit after '{$sign}' at line {$line}, column {$col}");
        }

        $full = $sign.$intPart;
        $isFloat = false;

        // Fractional part
        if (!$this->stream->isEndOfFile() && '.' === $this->stream->peek()) {
            $this->stream->consume(); // .
            $frac = $this->stream->consumeMatching('[0-9][0-9_]*');
            if ('' === $frac) {
                throw new SyntaxException("Expected digit after '.' in float at line {$line}, column {$col}");
            }
            $full .= '.'.$frac;
            $isFloat = true;
        }

        // Exponent part
        if (!$this->stream->isEndOfFile() && $this->stream->matches('[eE]')) {
            $e = $this->stream->consume();
            $expSign = '';
            if (!$this->stream->isEndOfFile() && $this->stream->matches('[+-]')) {
                $expSign = $this->stream->consume();
            }
            $expDigits = $this->stream->consumeMatching('[0-9][0-9_]*');
            if ('' === $expDigits) {
                throw new SyntaxException("Expected digit in exponent at line {$line}, column {$col}");
            }
            $full .= $e.$expSign.$expDigits;
            $isFloat = true;
        }

        // Validate underscores in the number
        $this->validateUnderscores($full, $line, $col);

        if ($isFloat) {
            return new Token(TokenType::T_FLOAT, $full, $line, $col);
        }

        return new Token(TokenType::T_INTEGER, $full, $line, $col);
    }

    /**
     * Validate underscore rules in a numeric literal:
     * - No leading underscore (after prefix like 0x)
     * - No trailing underscore
     * - No double underscores
     * - Underscore must be between digits
     */
    private function validateUnderscores(string $value, int $line, int $col): void
    {
        // Remove optional sign and prefix for validation
        $toCheck = $value;

        if (str_contains($toCheck, '__')) {
            throw new SyntaxException("Double underscore in number at line {$line}, column {$col}");
        }
        if (str_ends_with($toCheck, '_')) {
            throw new SyntaxException("Trailing underscore in number at line {$line}, column {$col}");
        }

        // Check underscore after prefix (0x_, 0o_, 0b_)
        if (preg_match('/^[+-]?0[xXoObB]_/', $toCheck)) {
            throw new SyntaxException("Underscore after prefix in number at line {$line}, column {$col}");
        }

        // Check underscore before/after dot
        if (str_contains($toCheck, '_.') || str_contains($toCheck, '._')) {
            throw new SyntaxException("Underscore adjacent to dot in number at line {$line}, column {$col}");
        }

        // Check underscore before/after exponent
        if (preg_match('/_[eE]|[eE][+-]?_/', $toCheck)) {
            throw new SyntaxException("Underscore adjacent to exponent in number at line {$line}, column {$col}");
        }
    }

    private function isNewline(string $char): bool
    {
        return "\n" === $char || "\r" === $char;
    }

    private function isWhitespace(string $char): bool
    {
        return ' ' === $char || "\t" === $char;
    }

    private function isDigit(string $char): bool
    {
        return $char >= '0' && $char <= '9';
    }

    private function isBareKeyChar(string $char): bool
    {
        return ($char >= 'A' && $char <= 'Z')
            || ($char >= 'a' && $char <= 'z')
            || ($char >= '0' && $char <= '9')
            || '_' === $char
            || '-' === $char;
    }
}
