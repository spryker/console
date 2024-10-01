<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Communication\Bootstrap;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Console\ConsoleConfig getConfig()
 * @method \Spryker\Zed\Console\Communication\ConsoleCommunicationFactory getFactory()
 * @method \Spryker\Zed\Console\Business\ConsoleFacade getFacade()
 */
class ProcessConsoleBootstrap extends ConsoleBootstrap
{
    /**
     * Gets the default input definition.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinitions = parent::getDefaultInputDefinition();
        $inputDefinitions->addOption(new InputOption('--max-duration', '', InputOption::VALUE_OPTIONAL, ''));
        $inputDefinitions->addOption(new InputOption('--min-duration', '', InputOption::VALUE_OPTIONAL, ''));

        return $inputDefinitions;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        $maxProcessDuration = $input->getParameterOption('--max-duration', $this->getConfig()->getMaxRepeatableExecutionDuration());
        $minDuration = $input->getParameterOption('--min-duration', $this->getConfig()->getMinRepeatableExecutionDuration());
        $startProcessTime = $this->microTime();

        do {
            $startCommandExecutionTime = $this->microTime();
            $exitCode = parent::doRunCommand(clone $command, clone $input, $output);

            $stopCommandExecutionTime = $this->microTime();
            $commandExecutionDuration = $stopCommandExecutionTime - $startCommandExecutionTime;
            $processExecutionDuration = $stopCommandExecutionTime - $startProcessTime;

            if ($minDuration > $commandExecutionDuration) {
                usleep((int)(($minDuration - $commandExecutionDuration) * 1e6));
            }

            $output->writeln('<fg=magenta>Process executed. Timer: ' . $processExecutionDuration . ' Code: ' . $exitCode . '</>');

            if ($exitCode !== 0) {
                return $exitCode;
            }
        } while ($maxProcessDuration > 0 && $processExecutionDuration + $commandExecutionDuration < $maxProcessDuration);

        return $exitCode;
    }

    /**
     * @return float
     */
    protected function microTime(): float
    {
        return microtime(true);
    }
}
