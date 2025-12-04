<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Console;

use Spryker\Shared\Application\ApplicationInterface;
use Spryker\Shared\Application\Kernel;
use Spryker\Shared\Console\Exception\ConsoleException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method getFactory() will be implemented by the concrete implementation.
 * @method getConfig() will be implemented by the concrete implementation.
 */
abstract class AbstractConsoleApplication extends Application
{
    public function __construct()
    {
        $this->initializeEnvironment();

        $application = $this->getApplication();

        /** @var \Spryker\Service\Container\ContainerInterface $container */
        $container = $application->getContainer();

        parent::__construct(new Kernel($container, false /*str_contains(APPLICATION_ENV, '.dev')*/));

        if (method_exists($application, 'registerPluginsAndBoot')) {
            $application->registerPluginsAndBoot($container);
        }

        $this->setCatchExceptions($this->getConfig()->shouldCatchExceptions());
    }

    protected function addEventListener(): void
    {
        $container = $this->getKernel()->getContainer();

        if (!$container->has('event_dispatcher')) {
            $factory = $this->getFactory();

            if (!method_exists($factory, 'createEventDispatcher')) {
                throw new ConsoleException(
                    sprintf(
                        'The EventDispatcher is missing in your Console setup for %s. ' .
                        'Add the appropriate EventDispatcherApplicationPlugin from the Console namespace ' .
                        '(e.g., `\\Spryker\\Zed\\EventDispatcher\\Communication\\Plugin\\Console\\EventDispatcherApplicationPlugin` for Zed, ' .
                        '`\\Spryker\\Yves\\EventDispatcher\\Plugin\\Console\\EventDispatcherApplicationPlugin` for Yves, or ' .
                        '`\\Spryker\\Glue\\EventDispatcher\\Plugin\\Console\\EventDispatcherApplicationPlugin` for Glue) ' .
                        'to the plugin stack returned by `ConsoleDependencyProvider::getApplicationPlugins()` method.',
                        static::class,
                    ),
                );
            }

            // Factory type varies by implementation; method existence already verified above
            /** @phpstan-ignore method.nonObject */
            $eventDispatcher = $factory->createEventDispatcher();
            $container->set('event_dispatcher', $eventDispatcher);
        }

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');
        $eventSubscriber = $this->getFactory()->getEventSubscriber();

        foreach ($eventSubscriber as $subscriber) {
            $eventDispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        $locatedCommands = $this->getFactory()->getConsoleCommands();

        foreach ($locatedCommands as $command) {
            $commands[$command->getName()] = $command;
        }

        return $commands;
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinitions = parent::getDefaultInputDefinition();
        $inputDefinitions->addOption(new InputOption('--no-pre', '', InputOption::VALUE_NONE, 'Will not execute pre run hooks'));
        $inputDefinitions->addOption(new InputOption('--no-post', '', InputOption::VALUE_NONE, 'Will not execute post run hooks'));
        $inputDefinitions->addOption(new InputOption('--quiet-meta', '', InputOption::VALUE_NONE, 'Disables meta output of store and environment'));
        $inputDefinitions->addOption(new InputOption('--repeatable', '', InputOption::VALUE_OPTIONAL, 'Enables multiple executions of the command until a certain duration', $this->getConfig()->getMaxRepeatableExecutionDuration()));
        $inputDefinitions->addOption(new InputOption('--max-duration', '-max', InputOption::VALUE_OPTIONAL, 'Maximum duration of the repeatable execution in seconds', $this->getConfig()->getMaxRepeatableExecutionDuration()));
        $inputDefinitions->addOption(new InputOption('--min-duration', '-min', InputOption::VALUE_OPTIONAL, 'Minimum duration of the repeatable execution in seconds', $this->getConfig()->getMinRepeatableExecutionDuration()));

        return $inputDefinitions;
    }

    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->setDecorated($output);

        if (!$input->hasParameterOption(['--format'], true) && !$input->hasParameterOption('--quiet-meta', true)) {
            $output->writeln($this->getInfoText($input));
        }

        if (!$input->hasParameterOption(['--no-pre'], true)) {
            $this->preRun($input, $output);
        }

        $this->getKernel()->boot();
        $this->addEventListener();

        $response = parent::doRun($input, $output);

        if (!$input->hasParameterOption(['--no-post'], true)) {
            $this->postRun($input, $output);
        }

        return $response;
    }

    abstract protected function initializeEnvironment(): void;

    abstract protected function getApplication(): ApplicationInterface;

    abstract protected function preRun(InputInterface $input, OutputInterface $output): void;

    abstract protected function postRun(InputInterface $input, OutputInterface $output): void;

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (!$input->hasParameterOption('--repeatable')) {
            return parent::doRunCommand($command, $input, $output);
        }

        $maxProcessDuration = $input->getParameterOption('--max-duration');
        $minProcessDuration = $input->getParameterOption('--min-duration');
        $startProcessTime = microtime(true);

        do {
            $startCommandTime = microtime(true);
            $exitCode = parent::doRunCommand($command, $input, $output);

            $stopCommandTime = microtime(true);
            $commandDuration = $stopCommandTime - $startCommandTime;
            $processDuration = $stopCommandTime - $startProcessTime;

            if ($minProcessDuration > $commandDuration) {
                usleep((int)(($minProcessDuration - $commandDuration) * 1e6));
            }

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    sprintf(
                        '<fg=magenta>Process executed. Duration: %s. Exit code: %s</>',
                        $processDuration,
                        $exitCode,
                    ),
                );
            }

            if ($exitCode !== 0) {
                return $exitCode;
            }
        } while ($maxProcessDuration > 0 && $processDuration + $commandDuration < $maxProcessDuration);

        return $exitCode;
    }

    protected function getInfoText(InputInterface $input): string
    {
        $infoTextData = [];

        if (defined('APPLICATION_REGION')) {
            $infoTextData[] = sprintf(
                '<fg=yellow>Region</fg=yellow>: <info>%s</info>',
                APPLICATION_REGION,
            );
        }

        $store = $this->getStore($input);

        if ($store) {
            $infoTextData[] = sprintf(
                '<fg=yellow>Store</fg=yellow>: <info>%s</info>',
                $store,
            );
        }

        if (defined('APPLICATION_CODE_BUCKET') && APPLICATION_CODE_BUCKET) {
            $infoTextData[] = sprintf(
                '<fg=yellow>Code bucket</fg=yellow>: <info>%s</info>',
                APPLICATION_CODE_BUCKET,
            );
        }

        $infoTextData[] = sprintf(
            '<fg=yellow>Environment</fg=yellow>: <info>%s</info>',
            APPLICATION_ENV,
        );

        return implode(' | ', $infoTextData);
    }

    /**
     * This will force color mode when executed from another tool. The env variable can be set
     * from anybody who wants to force color mode for the execution of this Application.
     *
     * For Spryker's deploy tool it is needed to get colored output from the console commands
     * executed by this script without force projects to deal with ANSI Escape sequences of the underlying
     * console commands.
     */
    protected function setDecorated(OutputInterface $output): void
    {
        if (getenv('FORCE_COLOR_MODE')) {
            $output->setDecorated(true);
        }
    }

    protected function getStore(InputInterface $input): string
    {
        $store = '';

        if ($input->hasParameterOption(['store'], true)) {
            $store = $input->getParameterOption('store');
        }

        if (!$store && defined('APPLICATION_STORE')) {
            $store = APPLICATION_STORE;
        }

        return $store;
    }
}
