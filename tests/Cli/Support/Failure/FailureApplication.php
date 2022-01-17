<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console\Tests\Cli\Support\Failure;

use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class FailureApplication extends Application
{
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        throw new RuntimeException('Application failed');
    }

    public function start(): void
    {
    }

    public function shutdown(int $exitCode): void
    {
    }

    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $output->writeln('Application failed', OutputInterface::VERBOSITY_QUIET);
    }
}
