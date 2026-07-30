<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Business\CommandCache;

interface ConsoleCommandCacheInterface
{
    /**
     * Returns the single invoked command when it can be served from a fresh cache, otherwise the full
     * command list produced by the injected provider (which is then cached for subsequent runs).
     *
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getCommands(): array;
}
