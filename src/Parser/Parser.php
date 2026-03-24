<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

use HypnoTox\Toml\Exception\SyntaxException;
use HypnoTox\Toml\Parser\Lexer\Lexer;
use HypnoTox\Toml\Parser\Lexer\LexerContext;
use HypnoTox\Toml\Parser\Token\Token;
use HypnoTox\Toml\Parser\Token\TokenType;
use HypnoTox\Toml\Toml;
use HypnoTox\Toml\TomlInterface;
use Override;

/**
 * @internal
 */
final class Parser implements ParserInterface
{
    #[Override]
    public function parse(string $input): TomlInterface
    {
        // Strip UTF-8 BOM
        if (str_starts_with($input, "\xEF\xBB\xBF")) {
            $input = substr($input, 3);
        }

        $lexer = new Lexer($input);
        $root = new TomlTable();
        /** @var list<string> $currentPath */
        $currentPath = [];

        while (true) {
            $token = $lexer->next(LexerContext::LineStart);

            if (TokenType::T_EOF === $token->type) {
                break;
            }

            if (TokenType::T_NEWLINE === $token->type || TokenType::T_COMMENT === $token->type || TokenType::T_WHITESPACE === $token->type) {
                continue;
            }

            if (TokenType::T_LEFT_BRACKET === $token->type) {
                $keyPath = $this->parseKeyPath($lexer);
                $this->expectToken($lexer, LexerContext::AfterKey, TokenType::T_RIGHT_BRACKET);
                $this->expectEndOfLine($lexer);

                $this->defineExplicitTable($root, $keyPath, $token->line);
                $currentPath = $keyPath;

                continue;
            }

            if (TokenType::T_DOUBLE_LEFT_BRACKET === $token->type) {
                $keyPath = $this->parseKeyPath($lexer);
                $this->expectToken($lexer, LexerContext::AfterKey, TokenType::T_DOUBLE_RIGHT_BRACKET);
                $this->expectEndOfLine($lexer);

                $this->defineArrayTable($root, $keyPath, $token->line);
                $currentPath = $keyPath;

                continue;
            }

            // Key-value pair
            $keyPath = $this->parseKeyPathStartingWith($lexer, $token);
            $this->skipWhitespace($lexer, LexerContext::AfterKey);
            $this->expectToken($lexer, LexerContext::AfterKey, TokenType::T_EQUALS);
            $this->skipWhitespace($lexer, LexerContext::Value);
            $value = $this->parseValue($lexer);
            $this->expectEndOfLine($lexer);

            $this->defineKeyValue($root, $currentPath, $keyPath, $value, $token->line);
        }

        return new Toml($root);
    }

    // ── Table/key definition ─────────────

    /**
     * Validate and create a [table] header path in the tree.
     *
     * @param non-empty-list<string> $keyPath
     *
     * @throws SyntaxException
     */
    private function defineExplicitTable(TomlTable $root, array $keyPath, int $line): void
    {
        $current = $root;
        $lastIdx = \count($keyPath) - 1;

        for ($i = 0; $i <= $lastIdx; ++$i) {
            $key = $keyPath[$i];
            $child = $current->get($key);
            $isFinal = ($i === $lastIdx);

            if (null === $child) {
                $child = new TomlTable($isFinal ? TableOrigin::Explicit : TableOrigin::Implicit);
                $current->set($key, $child);
                $current = $child;

                continue;
            }

            if ($child instanceof TomlValue) {
                throw new SyntaxException($isFinal
                    ? "Cannot define table, key already has a value at line {$line}"
                    : "Cannot define table, parent key already has a value at line {$line}");
            }

            if ($child instanceof TomlArray) {
                if ($isFinal) {
                    throw new SyntaxException("Cannot define table, already defined as array of tables at line {$line}");
                }
                // Only navigate through actual AoT arrays, not value arrays like a = [{...}]
                if ([] === $child->items || !$child->items[0] instanceof TomlTable || TableOrigin::ArrayOfTables !== $child->items[0]->origin) {
                    throw new SyntaxException("Cannot define table, parent key already has a value at line {$line}");
                }
                /** @psalm-suppress InvalidArrayOffset AoT arrays are always non-empty */
                $last = $child->items[\count($child->items) - 1];
                \assert($last instanceof TomlTable);
                $current = $last;

                continue;
            }

            \assert($child instanceof TomlTable);

            if (TableOrigin::Inline === $child->origin) {
                throw new SyntaxException("Cannot extend inline table at line {$line}");
            }

            if ($isFinal) {
                if (TableOrigin::Explicit === $child->origin) {
                    throw new SyntaxException("Cannot redefine table at line {$line}");
                }
                if (TableOrigin::ImplicitDotted === $child->origin) {
                    throw new SyntaxException("Cannot define table header for path already defined via dotted keys at line {$line}");
                }
                $child->origin = TableOrigin::Explicit;
            }

            $current = $child;
        }
    }

    /**
     * Validate and create a [[array-of-tables]] header path in the tree.
     *
     * @param non-empty-list<string> $keyPath
     *
     * @throws SyntaxException
     */
    private function defineArrayTable(TomlTable $root, array $keyPath, int $line): void
    {
        $current = $root;
        $lastIdx = \count($keyPath) - 1;

        for ($i = 0; $i <= $lastIdx; ++$i) {
            $key = $keyPath[$i];
            $child = $current->get($key);
            $isFinal = ($i === $lastIdx);

            if (null === $child) {
                if ($isFinal) {
                    $current->set($key, new TomlArray([new TomlTable(TableOrigin::ArrayOfTables)]));
                } else {
                    $child = new TomlTable(TableOrigin::Implicit);
                    $current->set($key, $child);
                    $current = $child;
                }

                continue;
            }

            if ($child instanceof TomlValue) {
                throw new SyntaxException("Cannot define array of tables, key already has a value at line {$line}");
            }

            if ($child instanceof TomlArray) {
                if ($isFinal) {
                    // Only allow appending if this is a real AoT (not a value array like a = [1,2])
                    if ([] === $child->items || !$child->items[0] instanceof TomlTable || TableOrigin::ArrayOfTables !== $child->items[0]->origin) {
                        throw new SyntaxException("Cannot define array of tables, key already has a value at line {$line}");
                    }
                    $child->items[] = new TomlTable(TableOrigin::ArrayOfTables);
                } else {
                    // Only navigate through actual AoT arrays
                    if ([] === $child->items || !$child->items[0] instanceof TomlTable || TableOrigin::ArrayOfTables !== $child->items[0]->origin) {
                        throw new SyntaxException("Cannot define array of tables, parent key already has a value at line {$line}");
                    }
                    /** @psalm-suppress InvalidArrayOffset AoT arrays are always non-empty */
                    $last = $child->items[\count($child->items) - 1];
                    \assert($last instanceof TomlTable);
                    $current = $last;
                }

                continue;
            }

            \assert($child instanceof TomlTable);

            if (TableOrigin::Inline === $child->origin) {
                throw new SyntaxException("Cannot extend inline table at line {$line}");
            }

            if ($isFinal) {
                if (TableOrigin::Explicit === $child->origin) {
                    throw new SyntaxException("Cannot define array of tables, already defined as table at line {$line}");
                }
                throw new SyntaxException("Cannot define array of tables, key already has a value at line {$line}");
            }

            $current = $child;
        }
    }

    /**
     * Validate and set a key = value pair, including dotted key intermediate validation.
     *
     * @param list<string>           $currentPath Current [table] scope
     * @param non-empty-list<string> $keyPath     Key path (may be dotted)
     *
     * @throws SyntaxException
     */
    private function defineKeyValue(TomlTable $root, array $currentPath, array $keyPath, TomlNode $value, int $line): void
    {
        // Navigate to current scope
        $current = $this->navigateToTable($root, $currentPath);

        $lastIdx = \count($keyPath) - 1;

        for ($i = 0; $i <= $lastIdx; ++$i) {
            $key = $keyPath[$i];
            $isFinal = ($i === $lastIdx);

            if ($isFinal) {
                $existing = $current->get($key);

                if (null !== $existing) {
                    throw new SyntaxException("Cannot redefine key at line {$line}");
                }

                $current->set($key, $value);
            } else {
                // Dotted key intermediate
                $child = $current->get($key);

                if (null === $child) {
                    $child = new TomlTable(TableOrigin::ImplicitDotted);
                    $current->set($key, $child);
                    $current = $child;

                    continue;
                }

                if ($child instanceof TomlValue || $child instanceof TomlArray) {
                    throw new SyntaxException("Cannot use key as table, already defined as value at line {$line}");
                }

                \assert($child instanceof TomlTable);

                if (TableOrigin::Inline === $child->origin) {
                    throw new SyntaxException("Cannot extend inline table at line {$line}");
                }
                if (TableOrigin::Explicit === $child->origin) {
                    throw new SyntaxException("Cannot extend explicitly defined table via dotted key at line {$line}");
                }

                // Promote Implicit → ImplicitDotted (dotted keys seal the table against [header] reopening)
                if (null === $child->origin || TableOrigin::Implicit === $child->origin) {
                    $child->origin = TableOrigin::ImplicitDotted;
                }

                $current = $child;
            }
        }
    }

    // ── Tree navigation ─────────────────────────────────────────────────

    /**
     * Navigate to a table at the given path, following array-of-tables to their last entry.
     *
     * @param list<string> $path
     */
    private function navigateToTable(TomlTable $root, array $path): TomlTable
    {
        $current = $root;
        foreach ($path as $segment) {
            $child = $current->get($segment);

            if (null === $child) {
                $child = new TomlTable();
                $current->set($segment, $child);
            }

            if ($child instanceof TomlArray) {
                /** @psalm-suppress InvalidArrayOffset AoT arrays are always non-empty when navigated */
                $last = $child->items[\count($child->items) - 1];
                \assert($last instanceof TomlTable);
                $current = $last;
            } else {
                \assert($child instanceof TomlTable);
                $current = $child;
            }
        }

        return $current;
    }

    /**
     * Set a value at a nested key path within an inline table.
     *
     * @param non-empty-list<string> $keyPath
     */
    private function setNestedValueInTable(TomlTable $table, array $keyPath, TomlNode $value): void
    {
        $current = $table;
        $lastIndex = \count($keyPath) - 1;

        for ($i = 0; $i < $lastIndex; ++$i) {
            $child = $current->get($keyPath[$i]);

            if (null === $child) {
                $child = new TomlTable();
                $current->set($keyPath[$i], $child);
            }

            \assert($child instanceof TomlTable);
            $current = $child;
        }

        $current->set($keyPath[$lastIndex], $value);
    }

    // ── Token parsing ───────────────────────────────────────────────────

    /**
     * @return non-empty-list<string>
     */
    private function parseKeyPath(Lexer $lexer): array
    {
        $keys = [];
        $this->skipWhitespace($lexer, LexerContext::Key);

        $keys[] = $this->parseKeySegment($lexer);

        while (true) {
            $this->skipWhitespace($lexer, LexerContext::AfterKey);
            $peeked = $lexer->peek(LexerContext::AfterKey);

            if (TokenType::T_DOT !== $peeked->type) {
                break;
            }

            $lexer->next(LexerContext::AfterKey); // consume dot
            $this->skipWhitespace($lexer, LexerContext::Key);
            $keys[] = $this->parseKeySegment($lexer);
        }

        return $keys;
    }

    /**
     * @return non-empty-list<string>
     */
    private function parseKeyPathStartingWith(Lexer $lexer, Token $firstToken): array
    {
        $keys = [$this->tokenToKeyString($firstToken)];

        while (true) {
            $this->skipWhitespace($lexer, LexerContext::AfterKey);
            $peeked = $lexer->peek(LexerContext::AfterKey);

            if (TokenType::T_DOT !== $peeked->type) {
                break;
            }

            $lexer->next(LexerContext::AfterKey); // consume dot
            $this->skipWhitespace($lexer, LexerContext::Key);
            $keys[] = $this->parseKeySegment($lexer);
        }

        return $keys;
    }

    private function parseKeySegment(Lexer $lexer): string
    {
        $token = $lexer->next(LexerContext::Key);

        return $this->tokenToKeyString($token);
    }

    private function tokenToKeyString(Token $token): string
    {
        return match ($token->type) {
            TokenType::T_BARE_KEY,
            TokenType::T_BASIC_STRING,
            TokenType::T_LITERAL_STRING => (string) $token->value,
            default => throw new SyntaxException("Expected key, got {$token->type->name} at line {$token->line}, column {$token->column}"),
        };
    }

    // ── Value parsing ───────────────────────────────────────────────────

    private function parseValue(Lexer $lexer): TomlNode
    {
        $token = $lexer->next(LexerContext::Value);

        /** @var string $tokenValue */
        $tokenValue = $token->value;

        return match ($token->type) {
            TokenType::T_BASIC_STRING,
            TokenType::T_LITERAL_STRING,
            TokenType::T_ML_BASIC_STRING,
            TokenType::T_ML_LITERAL_STRING => new TomlValue(ValueType::String, $tokenValue),
            TokenType::T_INTEGER => new TomlValue(ValueType::Integer, $this->parseInteger($token)),
            TokenType::T_HEX_INTEGER => new TomlValue(ValueType::Integer, $this->parseHexInteger($token)),
            TokenType::T_OCT_INTEGER => new TomlValue(ValueType::Integer, $this->parseOctInteger($token)),
            TokenType::T_BIN_INTEGER => new TomlValue(ValueType::Integer, $this->parseBinInteger($token)),
            TokenType::T_FLOAT => new TomlValue(ValueType::Float, $this->parseFloat($token)),
            TokenType::T_BOOL => new TomlValue(ValueType::Bool, 'true' === $tokenValue),
            TokenType::T_OFFSET_DATETIME => new TomlValue(ValueType::OffsetDateTime, $this->parseAndValidateDatetime($tokenValue, true, $token)),
            TokenType::T_LOCAL_DATETIME => new TomlValue(ValueType::LocalDateTime, $this->parseAndValidateDatetime($tokenValue, false, $token)),
            TokenType::T_LOCAL_DATE => new TomlValue(ValueType::LocalDate, $this->validateLocalDate($tokenValue, $token)),
            TokenType::T_LOCAL_TIME => new TomlValue(ValueType::LocalTime, $this->validateLocalTime($tokenValue, $token)),
            TokenType::T_LEFT_BRACKET => $this->parseArray($lexer),
            TokenType::T_LEFT_BRACE => $this->parseInlineTable($lexer),
            default => throw new SyntaxException("Expected value, got {$token->type->name} at line {$token->line}, column {$token->column}"),
        };
    }

    private function parseArray(Lexer $lexer): TomlArray
    {
        /** @var list<TomlNode> $items */
        $items = [];

        while (true) {
            $this->skipWhitespaceAndNewlines($lexer, LexerContext::ArrayItem);

            $peeked = $lexer->peek(LexerContext::ArrayItem);

            if (TokenType::T_RIGHT_BRACKET === $peeked->type) {
                $lexer->next(LexerContext::ArrayItem);

                break;
            }

            if (TokenType::T_COMMENT === $peeked->type) {
                $lexer->next(LexerContext::ArrayItem);

                continue;
            }

            $items[] = $this->parseValue($lexer);

            $this->skipWhitespaceAndNewlines($lexer, LexerContext::ArrayItem);

            $peeked = $lexer->peek(LexerContext::ArrayItem);
            if (TokenType::T_COMMENT === $peeked->type) {
                $lexer->next(LexerContext::ArrayItem);
                $this->skipWhitespaceAndNewlines($lexer, LexerContext::ArrayItem);
                $peeked = $lexer->peek(LexerContext::ArrayItem);
            }

            if (TokenType::T_COMMA === $peeked->type) {
                $lexer->next(LexerContext::ArrayItem);

                continue;
            }

            if (TokenType::T_RIGHT_BRACKET === $peeked->type) {
                $lexer->next(LexerContext::ArrayItem);

                break;
            }

            throw new SyntaxException("Expected ',' or ']' in array at line {$peeked->line}, column {$peeked->column}");
        }

        return new TomlArray($items);
    }

    private function parseInlineTable(Lexer $lexer): TomlTable
    {
        $table = new TomlTable(TableOrigin::Inline);
        /** @var array<string, true> $localDefinedKeys */
        $localDefinedKeys = [];
        $first = true;

        while (true) {
            $this->skipWhitespace($lexer, LexerContext::InlineTable);

            $peeked = $lexer->peek(LexerContext::InlineTable);

            if (TokenType::T_RIGHT_BRACE === $peeked->type) {
                $lexer->next(LexerContext::InlineTable);

                break;
            }

            if (!$first) {
                throw new SyntaxException("Expected '}' in inline table at line {$peeked->line}, column {$peeked->column}");
            }

            $first = false;

            // Parse key-value pairs
            while (true) {
                $this->skipWhitespace($lexer, LexerContext::InlineTable);
                $keyPath = $this->parseKeyPath($lexer);
                $this->skipWhitespace($lexer, LexerContext::AfterKey);
                $this->expectToken($lexer, LexerContext::AfterKey, TokenType::T_EQUALS);
                $this->skipWhitespace($lexer, LexerContext::Value);
                $value = $this->parseValue($lexer);

                // Check for duplicate keys in inline table
                $keyPathStr = implode('.', $keyPath);
                if (isset($localDefinedKeys[$keyPathStr])) {
                    throw new SyntaxException("Duplicate key '{$keyPathStr}' in inline table");
                }
                $localDefinedKeys[$keyPathStr] = true;

                // Also check intermediate dotted key paths for conflicts
                for ($i = 1; $i < \count($keyPath); ++$i) {
                    $intermediateStr = implode('.', \array_slice($keyPath, 0, $i));
                    if (isset($localDefinedKeys[$intermediateStr])) {
                        throw new SyntaxException("Cannot use key '{$intermediateStr}' as table, already defined as value in inline table");
                    }
                }

                // Check that a defined key isn't later used as an intermediate
                foreach ($localDefinedKeys as $existingKey => $_) {
                    /** @psalm-suppress RedundantCastGivenDocblockType PHP coerces numeric string keys to int */
                    $existingKeyStr = (string) $existingKey;
                    if ($existingKeyStr !== $keyPathStr && str_starts_with($existingKeyStr, $keyPathStr.'.')) {
                        throw new SyntaxException("Cannot redefine key '{$keyPathStr}' in inline table");
                    }
                    if ($existingKeyStr !== $keyPathStr && str_starts_with($keyPathStr, $existingKeyStr.'.')) {
                        throw new SyntaxException("Cannot use key '{$existingKeyStr}' as table in inline table, already defined as value");
                    }
                }

                $this->setNestedValueInTable($table, $keyPath, $value);

                $this->skipWhitespace($lexer, LexerContext::InlineTableAfterValue);
                $next = $lexer->peek(LexerContext::InlineTableAfterValue);

                if (TokenType::T_COMMA === $next->type) {
                    $lexer->next(LexerContext::InlineTableAfterValue);

                    continue;
                }

                if (TokenType::T_RIGHT_BRACE === $next->type) {
                    $lexer->next(LexerContext::InlineTableAfterValue);

                    break;
                }

                throw new SyntaxException("Expected ',' or '}' in inline table at line {$next->line}, column {$next->column}");
            }

            break;
        }

        return $table;
    }

    // ── Scalar parsing helpers ──────────────────────────────────────────

    private function parseInteger(Token $token): int
    {
        $raw = str_replace('_', '', (string) $token->value);

        $digits = ltrim($raw, '+-');
        if (\strlen($digits) > 1 && '0' === $digits[0]) {
            throw new SyntaxException("Leading zeros in integer at line {$token->line}, column {$token->column}");
        }

        return (int) $raw;
    }

    private function parseHexInteger(Token $token): int
    {
        $raw = str_replace('_', '', (string) $token->value);
        $result = hexdec(substr($raw, 2));

        if (\is_float($result)) {
            throw new SyntaxException("Hex integer out of range at line {$token->line}, column {$token->column}");
        }

        return $result;
    }

    private function parseOctInteger(Token $token): int
    {
        $raw = str_replace('_', '', (string) $token->value);
        $result = octdec(substr($raw, 2));

        if (\is_float($result)) {
            throw new SyntaxException("Octal integer out of range at line {$token->line}, column {$token->column}");
        }

        return $result;
    }

    private function parseBinInteger(Token $token): int
    {
        $raw = str_replace('_', '', (string) $token->value);
        $result = bindec(substr($raw, 2));

        if (\is_float($result)) {
            throw new SyntaxException("Binary integer out of range at line {$token->line}, column {$token->column}");
        }

        return $result;
    }

    private function parseFloat(Token $token): float|string
    {
        /** @var string $raw */
        $raw = $token->value;

        if (\in_array($raw, ['inf', '+inf', '-inf', 'nan', '+nan', '-nan'], true)) {
            return match ($raw) {
                'inf', '+inf' => \INF,
                '-inf' => -\INF,
                /** @psalm-suppress UndefinedConstant Psalm crashes analyzing NAN constant on PHP 8.5 */
                default => fdiv(0, 0),
            };
        }

        $cleaned = str_replace('_', '', $raw);

        /** @var list<string> $parts */
        $parts = preg_split('/[eE]/', $cleaned, 2);
        $numPart = $parts[0];
        $numPartAbs = ltrim($numPart, '+-');
        if (\strlen($numPartAbs) > 1 && '0' === $numPartAbs[0] && '.' !== $numPartAbs[1]) {
            throw new SyntaxException("Leading zeros in float at line {$token->line}, column {$token->column}");
        }

        return (float) $cleaned;
    }

    // ── Datetime validation ─────────────────────────────────────────────

    private function normalizeDatetime(string $value): string
    {
        $value = (string) preg_replace('/(\d{4}-\d{2}-\d{2})[ tT](\d{2})/', '$1T$2', $value);
        $value = (string) preg_replace('/[zZ]$/', 'Z', $value);

        return $value;
    }

    private function parseAndValidateDatetime(string $value, bool $hasOffset, Token $token): string
    {
        $normalized = $this->normalizeDatetime($value);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/', $normalized, $m)) {
            $this->validateDateComponents((int) $m[1], (int) $m[2], (int) $m[3], $token);
            $this->validateTimeComponents((int) $m[4], (int) $m[5], (int) $m[6], $token);
        }

        if ($hasOffset && preg_match('/([+-])(\d{2}):(\d{2})$/', $normalized, $m)) {
            if ((int) $m[2] > 23 || (int) $m[3] > 59) {
                throw new SyntaxException("Invalid timezone offset at line {$token->line}, column {$token->column}");
            }
        }

        return $normalized;
    }

    private function validateLocalDate(string $value, Token $token): string
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            $this->validateDateComponents((int) $m[1], (int) $m[2], (int) $m[3], $token);
        }

        return $value;
    }

    private function validateLocalTime(string $value, Token $token): string
    {
        if (preg_match('/^(\d{2}):(\d{2}):(\d{2})/', $value, $m)) {
            $this->validateTimeComponents((int) $m[1], (int) $m[2], (int) $m[3], $token);
        }

        return $value;
    }

    private function validateDateComponents(int $year, int $month, int $day, Token $token): void
    {
        if ($month < 1 || $month > 12) {
            throw new SyntaxException("Invalid month {$month} at line {$token->line}, column {$token->column}");
        }
        if ($day < 1) {
            throw new SyntaxException("Invalid day {$day} at line {$token->line}, column {$token->column}");
        }

        $daysInMonth = match ($month) {
            1, 3, 5, 7, 8, 10, 12 => 31,
            4, 6, 9, 11 => 30,
            2 => $this->isLeapYear($year) ? 29 : 28,
            default => 31,
        };

        if ($day > $daysInMonth) {
            throw new SyntaxException("Invalid day {$day} for month {$month} at line {$token->line}, column {$token->column}");
        }
    }

    private function validateTimeComponents(int $hour, int $minute, int $second, Token $token): void
    {
        if ($hour > 23) {
            throw new SyntaxException("Invalid hour {$hour} at line {$token->line}, column {$token->column}");
        }
        if ($minute > 59) {
            throw new SyntaxException("Invalid minute {$minute} at line {$token->line}, column {$token->column}");
        }
        if ($second > 60) { // Allow 60 for leap seconds
            throw new SyntaxException("Invalid second {$second} at line {$token->line}, column {$token->column}");
        }
    }

    private function isLeapYear(int $year): bool
    {
        if (0 === $year % 400) {
            return true;
        }
        if (0 === $year % 100) {
            return false;
        }

        return 0 === $year % 4;
    }

    // ── Lexer helpers ───────────────────────────────────────────────────

    private function expectToken(Lexer $lexer, LexerContext $context, TokenType $expected): void
    {
        $token = $lexer->next($context);

        if ($token->type !== $expected) {
            throw new SyntaxException("Expected {$expected->name}, got {$token->type->name} at line {$token->line}, column {$token->column}");
        }
    }

    private function expectEndOfLine(Lexer $lexer): void
    {
        while (true) {
            $token = $lexer->next(LexerContext::LineStart);

            if (TokenType::T_WHITESPACE === $token->type) {
                continue;
            }
            if (TokenType::T_COMMENT === $token->type) {
                continue;
            }
            if (TokenType::T_NEWLINE === $token->type || TokenType::T_EOF === $token->type) {
                return;
            }

            throw new SyntaxException("Expected end of line, got {$token->type->name} at line {$token->line}, column {$token->column}");
        }
    }

    private function skipWhitespace(Lexer $lexer, LexerContext $context): void
    {
        while (true) {
            $peeked = $lexer->peek($context);
            if (TokenType::T_WHITESPACE !== $peeked->type) {
                return;
            }
            $lexer->next($context);
        }
    }

    private function skipWhitespaceAndNewlines(Lexer $lexer, LexerContext $context): void
    {
        while (true) {
            $peeked = $lexer->peek($context);
            if (TokenType::T_WHITESPACE !== $peeked->type && TokenType::T_NEWLINE !== $peeked->type) {
                return;
            }
            $lexer->next($context);
        }
    }
}
