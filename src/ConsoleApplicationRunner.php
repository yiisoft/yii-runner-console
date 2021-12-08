<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Console;

use ErrorException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use Yiisoft\Config\Config;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\NotFoundException;
use Yiisoft\Definitions\Exception\CircularReferenceException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Definitions\Exception\NotInstantiableException;
use Yiisoft\Yii\Console\Application;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Console\Output\ConsoleBufferedOutput;
use Yiisoft\Yii\Runner\BootstrapRunner;
use Yiisoft\Yii\Runner\ConfigFactory;
use Yiisoft\Yii\Runner\RunnerInterface;

/**
 * `ConsoleApplicationRunner` runs the Yii console application.
 */
final class ConsoleApplicationRunner implements RunnerInterface
{
    private bool $debug;
    private string $rootPath;
    private ?string $environment;
    private ?Config $config = null;
    private ?ContainerInterface $container = null;
    private ?string $bootstrapGroup = 'bootstrap-console';

    /**
     * @param string $rootPath The absolute path to the project root.
     * @param bool $debug Whether the debug mode is enabled.
     * @param string|null $environment The environment name.
     */
    public function __construct(string $rootPath, bool $debug, ?string $environment)
    {
        $this->rootPath = $rootPath;
        $this->debug = $debug;
        $this->environment = $environment;
    }

    /**
     * Returns a new instance with the specified bootstrap configuration group name.
     *
     * @param string $bootstrapGroup The bootstrap configuration group name.
     *
     * @return self
     */
    public function withBootstrap(string $bootstrapGroup): self
    {
        $new = clone $this;
        $new->bootstrapGroup = $bootstrapGroup;
        return $new;
    }

    /**
     * Returns a new instance and disables the use of bootstrap configuration group.
     *
     * @return self
     */
    public function withoutBootstrap(): self
    {
        $new = clone $this;
        $new->bootstrapGroup = null;
        return $new;
    }

    /**
     * Returns a new instance with the specified config instance {@see Config}.
     *
     * @param Config $config The config instance.
     *
     * @return self
     */
    public function withConfig(Config $config): self
    {
        $new = clone $this;
        $new->config = $config;
        return $new;
    }

    /**
     * Returns a new instance with the specified container instance {@see ContainerInterface}.
     *
     * @param ContainerInterface $container The container instance.
     *
     * @return self
     */
    public function withContainer(ContainerInterface $container): self
    {
        $new = clone $this;
        $new->container = $container;
        return $new;
    }

    /**
     * {@inheritDoc}
     *
     * @throws CircularReferenceException|ErrorException|Exception|InvalidConfigException
     * @throws ContainerExceptionInterface|NotFoundException|NotFoundExceptionInterface|NotInstantiableException
     */
    public function run(): void
    {
        $config = $this->config ?? ConfigFactory::create($this->rootPath, $this->environment);
        $container = $this->container ?? $this->createDefaultContainer($config);

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
        } catch (Throwable $throwable) {
            $application->renderThrowable($throwable, new ConsoleBufferedOutput());
        } finally {
            $application->shutdown($exitCode);
            exit($exitCode);
        }
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    private function createDefaultContainer(Config $config): Container
    {
        $containerConfig = ContainerConfig::create()->withValidate($this->debug);

        if ($config->has('console')) {
            $containerConfig = $containerConfig->withDefinitions($config->get('console'));
        }

        if ($config->has('providers-console')) {
            $containerConfig = $containerConfig->withProviders($config->get('providers-console'));
        }

        if ($config->has('delegates-console')) {
            $containerConfig = $containerConfig->withDelegates($config->get('delegates-console'));
        }

        return new Container($containerConfig);
    }

    private function runBootstrap(ContainerInterface $container, array $bootstrapList): void
    {
        (new BootstrapRunner($container, $bootstrapList))->run();
    }
}
