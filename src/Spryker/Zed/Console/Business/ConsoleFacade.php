<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Console\Business\ConsoleBusinessFactory getFactory()
 */
class ConsoleFacade extends AbstractFacade implements ConsoleFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getConsoleCommands()
    {
        return $this->getFactory()->getConsoleCommands();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function getEventSubscriber()
    {
        return $this->getFactory()->getEventSubscriber();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array<\Spryker\Shared\ApplicationExtension\Dependency\Plugin\ApplicationPluginInterface>
     */
    public function getApplicationPlugins(): array
    {
        return $this->getFactory()->getApplicationPlugins();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Console\Business\ConsoleFacadeInterface::getApplicationPlugins()} instead.
     *
     * @return array<\Silex\ServiceProviderInterface>
     */
    public function getServiceProviders()
    {
        return $this->getFactory()->getServiceProviders();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function preRun(InputInterface $input, OutputInterface $output)
    {
        $this->getFactory()->createConsoleRunnerHook()->preRun($input, $output);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    public function postRun(InputInterface $input, OutputInterface $output)
    {
        $this->getFactory()->createConsoleRunnerHook()->postRun($input, $output);
    }
}
