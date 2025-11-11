<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Console\Business\Model\Fixtures;

use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;

/**
 * @method \Spryker\Zed\Console\Business\ConsoleBusinessFactory getFactory()
 * @method \Spryker\Zed\Console\Business\ConsoleFacadeInterface getFacade()
 */
class ConsoleMock extends Console
{
    public function getFactory(): AbstractCommunicationFactory
    {
        return parent::getFactory();
    }

    public function getFacade(): AbstractFacade
    {
        return parent::getFacade();
    }

    public function getQueryContainer(): ?AbstractQueryContainer
    {
        return parent::getQueryContainer();
    }
}
