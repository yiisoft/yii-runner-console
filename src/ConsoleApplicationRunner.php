<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console;

use ErrorException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Throwable;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Di\NotFoundException;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Console\Output\ConsoleBufferedOutput;
use Yiisoft\Yii\Runner\ApplicationRunner;

/**
 * `ConsoleApplicationRunner` runs the Yii console application.
 */
final class ConsoleApplicationRunner extends ApplicationRunner
{
    /**
     * @param string $rootPath The absolute path to the project root.
     * @param bool $debug Whether the debug mode is enabled.
     * @param string|null $environment The environment name.
     */
    public function __construct(string $rootPath, bool $debug, ?string $environment)
    {
        parent::__construct($rootPath, $debug, 'console', $environment);
        $this->bootstrapGroup = 'bootstrap-console';
        $this->eventsGroup = 'events-console';
    }

    /**
     * {@inheritDoc}
     *
     * @throws CircularReferenceException|ErrorException|Exception|InvalidConfigException
     * @throws ContainerExceptionInterface|NotFoundException|NotFoundExceptionInterface|NotInstantiableException
     */
    public function run(): void
    {
        $this->runBootstrap();
        $this->checkEvents();

        /** @var Application $application */
        $application = $this->getContainer()->get(Application::class);
        $exitCode = ExitCode::UNSPECIFIED_ERROR;

        $input = new ArgvInput();
        $output = new ConsoleBufferedOutput();

        try {
            $application->start($input);
            $exitCode = $application->run($input, $output);
        } catch (Throwable $throwable) {
            $application->renderThrowable($throwable, $output);
        } finally {
            $application->shutdown($exitCode);
            exit($exitCode);
        }
    }
}
