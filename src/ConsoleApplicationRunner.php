<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console;

use ErrorException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
        parent::__construct($rootPath, $debug, $environment);
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
        $config = $this->getConfig();
        $container = $this->getContainer($config, 'console');

        $this->runBootstrap($config, $container);
        $this->checkEvents($config, $container);

        /** @var Application */
        $application = $container->get(Application::class);
        $exitCode = ExitCode::UNSPECIFIED_ERROR;

        try {
            $application->start();
            $exitCode = $application->run(null, new ConsoleBufferedOutput());
        } catch (Throwable $throwable) {
            $application->renderThrowable($throwable, new ConsoleBufferedOutput());
        } finally {
            $application->shutdown($exitCode);
            exit($exitCode);
        }
    }
}
