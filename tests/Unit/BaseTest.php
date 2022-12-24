<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Tests\Unit;

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected $backupStaticAttributes = [];
    protected $runTestInSeparateProcess = false;

    public static function setUpBeforeClass(): void
    {
        $_SERVER['VAR_DUMPER_FORMAT'] = 'cli';
    }
}
