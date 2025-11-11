<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Console;

use Spryker\Glue\Console\Environment\ConsoleEnvironment;
use Spryker\Glue\Kernel\BundleConfigResolverAwareTrait;
use Spryker\Glue\Kernel\FactoryResolverAwareTrait;
use Spryker\Shared\Application\ApplicationInterface;
use Spryker\Shared\Console\AbstractConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Glue\Console\ConsoleConfig getConfig()
 * @method \Spryker\Glue\Console\ConsoleFactory getFactory()
 */
class ConsoleApplication extends AbstractConsoleApplication
{
    use BundleConfigResolverAwareTrait;
    use FactoryResolverAwareTrait;

    protected function initializeEnvironment(): void
    {
        ConsoleEnvironment::initialize();
    }

    protected function getApplication(): ApplicationInterface
    {
        return $this->getFactory()
            ->createApplication()
            ->boot();
    }

    protected function preRun(InputInterface $input, OutputInterface $output): void
    {
        $this->getFactory()->createConsoleRunnerHook()->preRun($input, $output);
    }

    protected function postRun(InputInterface $input, OutputInterface $output): void
    {
        $this->getFactory()->createConsoleRunnerHook()->postRun($input, $output);
    }
}
