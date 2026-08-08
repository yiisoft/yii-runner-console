<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Codeception actor class, loaded via Codeception's own autoloading (not PSR-4), so the analyser
    // cannot resolve it.
    ->ignoreUnknownClasses(['Yiisoft\Yii\Runner\Console\Tests\CliTester'])
    // These are only referenced in @throws/@see PHPDoc annotations, not in actual code, so the analyser
    // doesn't detect the usage; they are genuinely required for consumers to configure the DI container
    // and config layer this package's runner wires together.
    ->ignoreErrorsOnPackages(
        ['yiisoft/config', 'yiisoft/definitions', 'yiisoft/di'],
        [ErrorType::UNUSED_DEPENDENCY],
    );
