<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit\Parser\Lexer;

use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Lexer\Lexer;
use HypnoTox\Toml\Parser\Lexer\LexerContext;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\Tests\Unit\BaseTest;

final class LexerTest extends BaseTest
{
    public function testBareKey(): void
    {
        $lexer = new Lexer('foo');
        $token = $lexer->next(LexerContext::Key);
        $this->assertSame(TokenType::T_BARE_KEY, $token->type);
        $this->assertSame('foo', $token->value);
    }

    public function testDottedKey(): void
    {
        $lexer = new Lexer('foo.bar');
        $this->assertSame(TokenType::T_BARE_KEY, $lexer->next(LexerContext::Key)->type);
        $this->assertSame(TokenType::T_DOT, $lexer->next(LexerContext::AfterKey)->type);
        $this->assertSame(TokenType::T_BARE_KEY, $lexer->next(LexerContext::Key)->type);
    }

    public function testBasicString(): void
    {
        $lexer = new Lexer('"hello world"');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_BASIC_STRING, $token->type);
        $this->assertSame('hello world', $token->value);
    }

    public function testBasicStringEscapes(): void
    {
        $lexer = new Lexer('"hello\\nworld\\t\\\\\\""');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame("hello\nworld\t\\\"", $token->value);
    }

    public function testUnicodeEscape(): void
    {
        $lexer = new Lexer('"\\u0041\\U00000042"');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame('AB', $token->value);
    }

    public function testLiteralString(): void
    {
        $lexer = new Lexer("'hello\\nworld'");
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_LITERAL_STRING, $token->type);
        $this->assertSame('hello\\nworld', $token->value);
    }

    public function testMultilineBasicString(): void
    {
        $lexer = new Lexer("\"\"\"\nhello\nworld\"\"\"");
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_ML_BASIC_STRING, $token->type);
        $this->assertSame("hello\nworld", $token->value);
    }

    public function testMultilineBasicStringLineEndingBackslash(): void
    {
        $lexer = new Lexer("\"\"\"\nhello \\\n  world\"\"\"");
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame('hello world', $token->value);
    }

    public function testMultilineLiteralString(): void
    {
        $lexer = new Lexer("'''\nhello\nworld'''");
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_ML_LITERAL_STRING, $token->type);
        $this->assertSame("hello\nworld", $token->value);
    }

    public function testInteger(): void
    {
        $lexer = new Lexer('42');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_INTEGER, $token->type);
        $this->assertSame('42', $token->value);
    }

    public function testSignedInteger(): void
    {
        $lexer = new Lexer('-17');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_INTEGER, $token->type);
        $this->assertSame('-17', $token->value);
    }

    public function testIntegerWithUnderscores(): void
    {
        $lexer = new Lexer('1_000_000');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_INTEGER, $token->type);
        $this->assertSame('1_000_000', $token->value);
    }

    public function testHexInteger(): void
    {
        $lexer = new Lexer('0xDEAD_BEEF');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_HEX_INTEGER, $token->type);
        $this->assertSame('0xDEAD_BEEF', $token->value);
    }

    public function testOctInteger(): void
    {
        $lexer = new Lexer('0o755');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_OCT_INTEGER, $token->type);
    }

    public function testBinInteger(): void
    {
        $lexer = new Lexer('0b11010110');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_BIN_INTEGER, $token->type);
    }

    public function testFloat(): void
    {
        $lexer = new Lexer('3.14');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_FLOAT, $token->type);
        $this->assertSame('3.14', $token->value);
    }

    public function testFloatWithExponent(): void
    {
        $lexer = new Lexer('1e10');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_FLOAT, $token->type);
        $this->assertSame('1e10', $token->value);
    }

    public function testSpecialFloats(): void
    {
        foreach (['inf', '+inf', '-inf', 'nan', '+nan', '-nan'] as $val) {
            $lexer = new Lexer($val);
            $token = $lexer->next(LexerContext::Value);
            $this->assertSame(TokenType::T_FLOAT, $token->type, "Failed for: {$val}");
        }
    }

    public function testBool(): void
    {
        $lexer = new Lexer('true');
        $this->assertSame(TokenType::T_BOOL, $lexer->next(LexerContext::Value)->type);

        $lexer = new Lexer('false');
        $this->assertSame(TokenType::T_BOOL, $lexer->next(LexerContext::Value)->type);
    }

    public function testOffsetDatetime(): void
    {
        $lexer = new Lexer('1979-05-27T07:32:00Z');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_OFFSET_DATETIME, $token->type);
        $this->assertSame('1979-05-27T07:32:00Z', $token->value);
    }

    public function testLocalDatetime(): void
    {
        $lexer = new Lexer('1979-05-27T07:32:00');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_LOCAL_DATETIME, $token->type);
    }

    public function testLocalDate(): void
    {
        $lexer = new Lexer('1979-05-27');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_LOCAL_DATE, $token->type);
    }

    public function testLocalTime(): void
    {
        $lexer = new Lexer('07:32:00');
        $token = $lexer->next(LexerContext::Value);
        $this->assertSame(TokenType::T_LOCAL_TIME, $token->type);
    }

    public function testTableHeader(): void
    {
        $lexer = new Lexer('[table]');
        $token = $lexer->next(LexerContext::LineStart);
        $this->assertSame(TokenType::T_LEFT_BRACKET, $token->type);
    }

    public function testArrayOfTablesHeader(): void
    {
        $lexer = new Lexer('[[array]]');
        $token = $lexer->next(LexerContext::LineStart);
        $this->assertSame(TokenType::T_DOUBLE_LEFT_BRACKET, $token->type);
    }

    public function testArrayValue(): void
    {
        $lexer = new Lexer('[1, 2]');
        $this->assertSame(TokenType::T_LEFT_BRACKET, $lexer->next(LexerContext::Value)->type);
        $this->assertSame(TokenType::T_INTEGER, $lexer->next(LexerContext::ArrayItem)->type);
        $this->assertSame(TokenType::T_COMMA, $lexer->next(LexerContext::ArrayItem)->type);
        $lexer->next(LexerContext::ArrayItem); // whitespace
        $this->assertSame(TokenType::T_INTEGER, $lexer->next(LexerContext::ArrayItem)->type);
        $this->assertSame(TokenType::T_RIGHT_BRACKET, $lexer->next(LexerContext::ArrayItem)->type);
    }

    public function testInlineTable(): void
    {
        $lexer = new Lexer('{key = "value"}');
        $this->assertSame(TokenType::T_LEFT_BRACE, $lexer->next(LexerContext::Value)->type);
        $this->assertSame(TokenType::T_BARE_KEY, $lexer->next(LexerContext::InlineTable)->type);
        $lexer->next(LexerContext::AfterKey); // whitespace
        $this->assertSame(TokenType::T_EQUALS, $lexer->next(LexerContext::AfterKey)->type);
        $lexer->next(LexerContext::Value); // whitespace
        $this->assertSame(TokenType::T_BASIC_STRING, $lexer->next(LexerContext::Value)->type);
        $this->assertSame(TokenType::T_RIGHT_BRACE, $lexer->next(LexerContext::InlineTableAfterValue)->type);
    }

    public function testLineTracking(): void
    {
        $lexer = new Lexer("foo\nbar");
        $t1 = $lexer->next(LexerContext::Key);
        $this->assertSame(1, $t1->line);
        $lexer->next(LexerContext::LineStart); // newline
        $t2 = $lexer->next(LexerContext::Key);
        $this->assertSame(2, $t2->line);
    }

    public function testPeekDoesNotAdvance(): void
    {
        $lexer = new Lexer('foo');
        $peeked = $lexer->peek(LexerContext::Key);
        $next = $lexer->next(LexerContext::Key);
        $this->assertSame($peeked->type, $next->type);
        $this->assertSame($peeked->value, $next->value);
    }

    public function testInvalidEscapeThrows(): void
    {
        $lexer = new Lexer('"\\q"');
        $this->expectException(SyntaxException::class);
        $lexer->next(LexerContext::Value);
    }

    public function testNewlineInBasicStringThrows(): void
    {
        $lexer = new Lexer("\"hello\nworld\"");
        $this->expectException(SyntaxException::class);
        $lexer->next(LexerContext::Value);
    }

    public function testUnexpectedCharAfterKeyThrows(): void
    {
        $lexer = new Lexer('!');
        $this->expectException(SyntaxException::class);
        $lexer->next(LexerContext::AfterKey);
    }
}
