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
    ->ignoreUnknownClasses(['Yiisoft\Yii\Runner\Console\Tests\CliTester']);
