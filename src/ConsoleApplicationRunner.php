<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console;

use Error;
use ErrorException;
use Exception;
use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;
use Yiisoft\Di\Container;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotFoundException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Console\Output\ConsoleBufferedOutput;
use Yiisoft\Yii\Runner\BootstrapRunner;
use Yiisoft\Yii\Runner\ConfigFactory;
use Yiisoft\Yii\Runner\RunnerInterface;

final class ConsoleApplicationRunner implements RunnerInterface
{
    private bool $debug;
    private string $rootPath;
    private ?string $environment;
    private ?Config $config = null;
    private ?ContainerInterface $container = null;
    private ?string $bootstrapGroup = 'bootstrap-console';

    public function __construct(string $rootPath, bool $debug, ?string $environment)
    {
        $this->rootPath = $rootPath;
        $this->debug = $debug;
        $this->environment = $environment;
    }

    public function withBootstrap(string $bootstrapGroup): self
    {
        $new = clone $this;
        $new->bootstrapGroup = $bootstrapGroup;
        return $new;
    }

    public function withoutBootstrap(): self
    {
        $new = clone $this;
        $new->bootstrapGroup = null;
        return $new;
    }

    public function withConfig(Config $config): self
    {
        $new = clone $this;
        $new->config = $config;
        return $new;
    }

    public function withContainer(ContainerInterface $container): self
    {
        $new = clone $this;
        $new->container = $container;
        return $new;
    }

    /**
     * @throws CircularReferenceException|ErrorException|Exception|InvalidConfigException|NotFoundException
     * @throws NotInstantiableException
     */
    public function run(): void
    {
        $config = $this->config ?? ConfigFactory::create($this->rootPath, $this->environment);

        $container = $this->container ?? new Container(
            $config->get('console'),
            $config->get('providers-console'),
            [],
            $this->debug,
            $config->get('delegates-console')
        );

        if ($container instanceof Container) {
            $container = $container->get(ContainerInterface::class);
        }

        // Run bootstrap
        if ($this->bootstrapGroup !== null) {
            $this->runBootstrap($container, $config->get($this->bootstrapGroup));
        }

        /** @var Application */
        $application = $container->get(Application::class);
        $exitCode = ExitCode::UNSPECIFIED_ERROR;

        try {
            $application->start();
            $exitCode = $application->run(null, new ConsoleBufferedOutput());
        } catch (Error $error) {
            $application->renderThrowable($error, new ConsoleBufferedOutput());
        } finally {
            $application->shutdown($exitCode);
            exit($exitCode);
        }
    }

    private function runBootstrap(ContainerInterface $container, array $bootstrapList): void
    {
        (new BootstrapRunner($container, $bootstrapList))->run();
    }
}
