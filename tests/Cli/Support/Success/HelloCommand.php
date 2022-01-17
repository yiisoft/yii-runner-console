<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console\Tests\Cli\Support\Success;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Yii\Console\ExitCode;

final class HelloCommand extends Command
{
    protected static $defaultName = 'hello';
    protected static $defaultDescription = 'Hello command';

    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello');
        return ExitCode::OK;
    }
}
