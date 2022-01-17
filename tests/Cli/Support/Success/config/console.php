<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\CommandLoader;
use Yiisoft\Yii\Runner\Console\Tests\Cli\Support\Success\HelloCommand;

return [
    Application::class => static function (ContainerInterface $container) {
        $application = new Application();
        $application->setCommandLoader(new CommandLoader($container, ['hello' => HelloCommand::class]));
        return $application;
    },
];
