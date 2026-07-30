<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console;

use Spryker\Zed\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\Console\ConsoleConfig getSharedConfig()
 */
class ConsoleConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    protected const SAPI_NAME_CONSOLE = 'cli';

    /**
     * @var string
     */
    protected const string CONSOLE_COMMAND_CACHE_FILE_PATH = '/data/cache/console/command-map.php';

    /**
     * Commands that resolve sibling commands at runtime via the Application (e.g. Application::find()).
     * They require the full command list to be registered, so the single-command cache is skipped for them.
     *
     * @var list<string>
     */
    protected const array CACHE_EXCLUDED_CONSOLE_COMMAND_NAMES = [
        'setup:install',
        'setup:deploy:prepare-propel',
        'propel:install',
        'propel:deploy:prepare',
        'multi-process:run',
        'dev:ide:generate-auto-completion',
        'dev:ide-auto-completion:remove',
        'dev:ide-auto-completion:generate',
    ];

    /**
     * @api
     *
     * @return bool
     */
    public function shouldCatchExceptions(): bool
    {
        return $this->getSharedConfig()->shouldCatchExceptions();
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isDebugModeEnabled(): bool
    {
        return $this->getSharedConfig()->isDebugModeEnabled();
    }

    /**
     * @api
     *
     * @return bool
     */
    public function isPhpSapiEqualCli(): bool
    {
        return PHP_SAPI === static::SAPI_NAME_CONSOLE;
    }

    /**
     * Specification:
     * - Returns the maximum duration (in seconds) of repeatable command execution.
     *
     * @api
     *
     * @return int
     */
    public function getMaxRepeatableExecutionDuration(): int
    {
        return $this->getSharedConfig()->getMaxRepeatableExecutionDuration();
    }

    /**
     * Specification:
     * - Returns the minimum duration (in seconds) of repeatable command execution.
     *
     * @api
     *
     * @return int
     */
    public function getMinRepeatableExecutionDuration(): int
    {
        return $this->getSharedConfig()->getMinRepeatableExecutionDuration();
    }

    /**
     * Specification:
     * - When enabled, the console caches a command name => class map and, on subsequent runs, instantiates
     *   only the invoked command instead of every registered command, reducing bootstrap time.
     *
     * @api
     *
     * @return bool
     */
    public function isConsoleCommandCacheEnabled(): bool
    {
        return true;
    }

    /**
     * Specification:
     * - Returns the absolute path of the file storing the cached command name => class map.
     *
     * @api
     *
     * @return string
     */
    public function getConsoleCommandCacheFilePath(): string
    {
        return APPLICATION_ROOT_DIR . static::CONSOLE_COMMAND_CACHE_FILE_PATH;
    }

    /**
     * Specification:
     * - Returns the command names that must never be served from the single-command cache because they
     *   resolve sibling commands at runtime and therefore require the full command list to be registered.
     *
     * @api
     *
     * @return list<string>
     */
    public function getCacheExcludedConsoleCommandNames(): array
    {
        return static::CACHE_EXCLUDED_CONSOLE_COMMAND_NAMES;
    }
}
