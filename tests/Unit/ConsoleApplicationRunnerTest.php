<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console\Tests\Unit;

use Codeception\PHPUnit\TestCase;
use Yiisoft\Yii\Runner\Console\ConsoleApplicationRunner;

final class ConsoleApplicationRunnerTest extends TestCase
{
    public function testConfigMergePlanFile(): void
    {
        $runner = new ConsoleApplicationRunner(
            rootPath: __DIR__ . '/Support/custom-merge-plan',
            configMergePlanFile: 'test-merge-plan.php',
        );

        $params = $runner->getConfig()->get('params-console');

        $this->assertSame(['a' => 42,], $params);
    }

    public function testConfigDirectory(): void
    {
        $runner = new ConsoleApplicationRunner(
            rootPath: __DIR__ . '/Support',
            configDirectory: 'custom-config',
        );

        $params = $runner->getConfig()->get('params-console');

        $this->assertSame(['age' => 22], $params);
    }
}
