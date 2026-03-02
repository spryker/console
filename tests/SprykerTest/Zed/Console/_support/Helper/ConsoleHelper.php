<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Console\Helper;

use SprykerTest\Shared\Console\Helper\ConsoleHelper as SharedConsoleHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @deprecated Use {@link \SprykerTest\Shared\Console\Helper\ConsoleHelper} instead.
 */
class ConsoleHelper extends SharedConsoleHelper
{
    public function getConsoleTester(Command $command): CommandTester
    {
        return parent::getConsoleTester($command);
    }
}
