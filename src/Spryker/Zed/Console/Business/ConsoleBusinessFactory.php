<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Business;

use Spryker\Shared\Console\Hook\ConsoleRunnerHook;
use Spryker\Zed\Console\Business\CommandCache\ConsoleCommandCache;
use Spryker\Zed\Console\Business\CommandCache\ConsoleCommandCacheInterface;
use Spryker\Zed\Console\ConsoleDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\Console\ConsoleConfig getConfig()
 */
class ConsoleBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getConsoleCommands(): array
    {
        return $this->createConsoleCommandCache()->getCommands();
    }

    public function createConsoleCommandCache(): ConsoleCommandCacheInterface
    {
        return new ConsoleCommandCache(
            $this->getConfig(),
            fn (): array => $this->getCommands(),
        );
    }

    /**
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getCommands(): array
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::COMMANDS);
    }

    /**
     * @return \Spryker\Shared\Console\Hook\ConsoleRunnerHookInterface
     */
    public function createConsoleRunnerHook()
    {
        return new ConsoleRunnerHook(
            $this->getPreRunHookPlugins(),
            $this->getPostRunHookPlugins(),
        );
    }

    /**
     * @return array<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function getEventSubscriber()
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
     * @deprecated Use {@link \Spryker\Zed\Console\Business\ConsoleBusinessFactory::getApplicationPlugins()} instead.
     *
     * @return array<\Silex\ServiceProviderInterface>
     */
    public function getServiceProviders()
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::SERVICE_PROVIDERS);
    }

    /**
     * @return array<\Spryker\Zed\Console\Dependency\Plugin\ConsolePreRunHookPluginInterface>
     */
    public function getPreRunHookPlugins()
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::PLUGINS_CONSOLE_PRE_RUN_HOOK);
    }

    /**
     * @return array<\Spryker\Zed\Console\Dependency\Plugin\ConsolePostRunHookPluginInterface>
     */
    public function getPostRunHookPlugins()
    {
        return $this->getProvidedDependency(ConsoleDependencyProvider::PLUGINS_CONSOLE_POST_RUN_HOOK);
    }
}
