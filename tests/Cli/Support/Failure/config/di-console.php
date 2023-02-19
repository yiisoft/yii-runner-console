<?php

declare(strict_types=1);

use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Runner\Console\Tests\Cli\Support\Failure\FailureApplication;

return [
    Application::class => new FailureApplication(),
];
