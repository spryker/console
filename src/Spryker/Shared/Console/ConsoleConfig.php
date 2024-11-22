<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Console;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class ConsoleConfig extends AbstractSharedConfig
{
    /**
     * @api
     *
     * @return bool
     */
    public function shouldCatchExceptions(): bool
    {
        return $this->get(ConsoleConstants::CATCH_EXCEPTIONS, false);
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isDebugModeEnabled(): bool
    {
        return $this->get(ConsoleConstants::IS_DEBUG_ENABLED, false);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getMaxRepeatableExecutionDuration(): int
    {
        return $this->get(ConsoleConstants::MAX_REPEATABLE_EXECUTION_DURATION, 0);
    }

    /**
     * @api
     *
     * @return int
     */
    public function getMinRepeatableExecutionDuration(): int
    {
        return $this->get(ConsoleConstants::MIN_REPEATABLE_EXECUTION_DURATION, 1);
    }
}
