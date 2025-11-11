<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Communication;

use Spryker\Shared\Application\ApplicationInterface;
use Spryker\Shared\Console\AbstractConsoleApplication;
use Spryker\Zed\Console\Business\Model\Environment;
use Spryker\Zed\Kernel\BundleConfigResolverAwareTrait;
use Spryker\Zed\Kernel\Communication\FacadeResolverAwareTrait;
use Spryker\Zed\Kernel\Communication\FactoryResolverAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Console\ConsoleConfig getConfig()
 * @method \Spryker\Zed\Console\Communication\ConsoleCommunicationFactory getFactory()
 * @method \Spryker\Zed\Console\Business\ConsoleFacade getFacade()
 */
class ConsoleApplication extends AbstractConsoleApplication
{
    use BundleConfigResolverAwareTrait;
    use FactoryResolverAwareTrait;
    use FacadeResolverAwareTrait;

    protected function initializeEnvironment(): void
    {
        Environment::initialize();
    }

    protected function getApplication(): ApplicationInterface
    {
        return $this->getFactory()
            ->createApplication()
            ->boot();
    }

    protected function preRun(InputInterface $input, OutputInterface $output): void
    {
        $this->getFacade()->preRun($input, $output);
    }

    protected function postRun(InputInterface $input, OutputInterface $output): void
    {
        $this->getFacade()->postRun($input, $output);
    }
}
