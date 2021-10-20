<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Console;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Shared\Console\Hook\ConsoleRunnerHook;
use Spryker\Shared\Console\Hook\ConsoleRunnerHookInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Shared\Console\Hook\ConsoleRunnerHookInterface
     */
    public function createConsoleRunnerHook(): ConsoleRunnerHookInterface
    {
        return new ConsoleRunnerHook(
            $this->getPreRunHookPlugins(),
            $this->getPostRunHookPlugins(),
        );
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getConsoleCommands(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::COMMANDS);
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function createEventDispatcher(): EventDispatcherInterface
    {
        return new EventDispatcher();
    }

    /**
     * @return array<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function getEventSubscriber(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::EVENT_SUBSCRIBER);
    }

    /**
     * @return array<\Spryker\Shared\ApplicationExtension\Dependency\Plugin\ApplicationPluginInterface>
     */
    public function getApplicationPlugins(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::PLUGINS_APPLICATION);
    }

    /**
     * @return array<\Spryker\Shared\Console\Dependency\Plugin\ConsolePreRunHookPluginInterface>
     */
    public function getPreRunHookPlugins(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::PLUGINS_CONSOLE_PRE_RUN_HOOK);
    }

    /**
     * @return array<\Spryker\Shared\Console\Dependency\Plugin\ConsolePostRunHookPluginInterface>
     */
    public function getPostRunHookPlugins(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::PLUGINS_CONSOLE_POST_RUN_HOOK);
    }
}
