<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Console\Bootstrap;

use Spryker\Yves\Console\Environment\ConsoleEnvironment;
use Spryker\Yves\Kernel\BundleConfigResolverAwareTrait;
use Spryker\Yves\Kernel\FactoryResolverAwareTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Yves\Console\ConsoleConfig getConfig()
 * @method \Spryker\Yves\Console\ConsoleFactory getFactory()
 */
class ConsoleBootstrap extends Application
{
    use BundleConfigResolverAwareTrait;
    use FactoryResolverAwareTrait;

    /**
     * @var string
     */
    protected const VERSION = '1';

    /**
     * @var string
     */
    protected const NAME = 'Spryker Yves Console';

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = self::NAME, $version = self::VERSION)
    {
        ConsoleEnvironment::initialize();

        parent::__construct($name, $version);

        $this->setCatchExceptions($this->getConfig()->shouldCatchExceptions());
        $this->addEventDispatcher();
    }

    /**
     * @return void
     */
    protected function addEventDispatcher(): void
    {
        $eventDispatcher = $this->getFactory()->createEventDispatcher();
        $eventSubscriber = $this->getFactory()->getEventSubscriber();

        foreach ($eventSubscriber as $subscriber) {
            $eventDispatcher->addSubscriber($subscriber);
        }

        $this->setDispatcher($eventDispatcher);
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

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinitions = parent::getDefaultInputDefinition();

        $inputDefinitions->addOption(new InputOption('--no-pre', '', InputOption::VALUE_NONE, 'Will not execute pre run hooks'));
        $inputDefinitions->addOption(new InputOption('--no-post', '', InputOption::VALUE_NONE, 'Will not execute post run hooks'));
        $inputDefinitions->addOption(new InputOption('--quiet-meta', '', InputOption::VALUE_NONE, 'Disables meta output of store and environment'));

        return $inputDefinitions;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->setDecorated($output);

        if (!$input->hasParameterOption(['--format'], true) && !$input->hasParameterOption('--quiet-meta', true)) {
            $output->writeln($this->getInfoText());
        }

        $this->getFactory()
            ->createApplication()
            ->boot();

        if (!$input->hasParameterOption(['--no-pre'], true)) {
            $this->getFactory()->createConsoleRunnerHook()->preRun($input, $output);
        }

        $response = parent::doRun($input, $output);

        if (!$input->hasParameterOption(['--no-post'], true)) {
            $this->getFactory()->createConsoleRunnerHook()->postRun($input, $output);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getInfoText(): string
    {
        return sprintf(
            '<fg=yellow>Code bucket</fg=yellow>: <info>%s</info> | <fg=yellow>Store</fg=yellow>: <info>%s</info> | <fg=yellow>Environment</fg=yellow>: <info>%s</info>',
            APPLICATION_CODE_BUCKET !== '' ? APPLICATION_CODE_BUCKET : 'N/A',
            defined('APPLICATION_STORE') ? APPLICATION_STORE : 'N/A',
            APPLICATION_ENV,
        );
    }

    /**
     * This will force color mode when executed from another tool. The env variable can be set
     * from anybody who wants to force color mode for the execution of this Application.
     *
     * For Spryker's deploy tool it is needed to get colored output from the console commands
     * executed by this script without force projects to deal with ANSI Escape sequences of the underlying
     * console commands.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function setDecorated(OutputInterface $output): void
    {
        if (getenv('FORCE_COLOR_MODE')) {
            $output->setDecorated(true);
        }
    }
}
