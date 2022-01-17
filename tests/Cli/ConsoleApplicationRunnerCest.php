#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console\Tests\Cli;

use Yiisoft\Yii\Runner\Console\Tests\CliTester;

final class ConsoleApplicationRunnerCest
{
    public function testSuccess(CliTester $I): void
    {
        $I->runShellCommand(__DIR__ . '/Support/Success/run');
        $I->seeInShellOutput('Yii Console');
    }

    public function testSuccessCommand(CliTester $I): void
    {
        $I->runShellCommand(__DIR__ . '/Support/Success/run hello');
        $I->dontSeeInShellOutput('Yii Console');
        $I->seeInShellOutput('Hello');
    }

    public function testFailure(CliTester $I): void
    {
        $I->runShellCommand(__DIR__ . '/Support/Failure/run', false);
        $I->dontSeeInShellOutput('Yii Console');
        $I->seeInShellOutput('Application failed');
    }
}
