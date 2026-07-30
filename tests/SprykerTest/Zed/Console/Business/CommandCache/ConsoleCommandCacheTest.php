<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Console\Business\CommandCache;

use Closure;
use Codeception\Test\Unit;
use Spryker\Zed\Cache\Communication\Console\EmptyAllCachesConsole;
use Spryker\Zed\Console\Business\CommandCache\ConsoleCommandCache;
use Spryker\Zed\Console\ConsoleConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Console
 * @group Business
 * @group CommandCache
 * @group ConsoleCommandCacheTest
 * Add your own group annotations below this line
 */
class ConsoleCommandCacheTest extends Unit
{
    public const string MAPPED_COMMAND_NAME = 'cache:empty-all';

    public const string EXCLUDED_COMMAND_NAME = 'propel:install';

    /**
     * @var array<string>|null
     */
    protected ?array $originalArgv = null;

    protected string $sourceFilePath = '';

    protected string $cacheFilePath = '';

    protected int $providerCallCount = 0;

    protected function _before(): void
    {
        $this->originalArgv = $_SERVER['argv'] ?? null;
        $this->providerCallCount = 0;

        $directory = sprintf('%s/console-command-cache-%s', sys_get_temp_dir(), uniqid('', true));
        mkdir($directory, 0775, true);

        $this->sourceFilePath = $directory . '/source.php';
        $this->cacheFilePath = $directory . '/cache/command-map.php';
        file_put_contents($this->sourceFilePath, '<?php // command provider');
    }

    protected function _after(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
    }

    public function testGetCommandsServesSingleCommandFromWarmCacheWithoutInvokingProvider(): void
    {
        // Arrange - a cold run builds the cache from the full command list.
        $this->setRequestedCommand(static::MAPPED_COMMAND_NAME);
        $this->createConsoleCommandCache()->getCommands();
        $this->providerCallCount = 0;

        // Act - a subsequent (warm) run for the same command.
        $commands = $this->createConsoleCommandCache()->getCommands();

        // Assert
        $this->assertCount(1, $commands, 'A warm cache must serve only the invoked command.');
        $this->assertSame(static::MAPPED_COMMAND_NAME, $commands[0]->getName());
        $this->assertSame(0, $this->providerCallCount, 'The full command list must not be produced on a cache hit.');
    }

    public function testGetCommandsReturnsFullListWhenCacheIsDisabled(): void
    {
        // Arrange
        $this->setRequestedCommand(static::MAPPED_COMMAND_NAME);
        $this->createConsoleCommandCache()->getCommands();
        $this->providerCallCount = 0;

        // Act
        $commands = $this->createConsoleCommandCache(false)->getCommands();

        // Assert
        $this->assertSame(1, $this->providerCallCount, 'A disabled cache must always produce the full command list.');
        $this->assertSame(static::MAPPED_COMMAND_NAME, $commands[0]->getName());
    }

    public function testGetCommandsReturnsFullListForExcludedCommand(): void
    {
        // Arrange
        $this->setRequestedCommand(static::MAPPED_COMMAND_NAME);
        $this->createConsoleCommandCache()->getCommands();
        $this->providerCallCount = 0;
        $this->setRequestedCommand(static::EXCLUDED_COMMAND_NAME);

        // Act
        $this->createConsoleCommandCache()->getCommands();

        // Assert
        $this->assertSame(1, $this->providerCallCount, 'Excluded orchestrator commands must never be served from cache.');
    }

    public function testGetCommandsRebuildsWhenSourceFileChanged(): void
    {
        // Arrange
        $this->setRequestedCommand(static::MAPPED_COMMAND_NAME);
        $this->createConsoleCommandCache()->getCommands();
        $this->providerCallCount = 0;

        // Act - the command-providing file changes, so the cached signature no longer matches.
        file_put_contents($this->sourceFilePath, '<?php // command provider changed with a different size');
        $this->createConsoleCommandCache()->getCommands();

        // Assert
        $this->assertSame(1, $this->providerCallCount, 'A changed source file must invalidate the cache and rebuild it.');
    }

    protected function createConsoleCommandCache(bool $isEnabled = true): ConsoleCommandCache
    {
        // Override getSourceFilePaths() so the freshness signature is driven by the test's temporary
        // source file instead of the real ConsoleDependencyProvider chain.
        return new class ($this->createConsoleConfig($isEnabled), $this->createCommandsProvider(), $this->sourceFilePath) extends ConsoleCommandCache {
            public function __construct(
                ConsoleConfig $consoleConfig,
                Closure $commandsProvider,
                protected string $testSourceFilePath
            ) {
                parent::__construct($consoleConfig, $commandsProvider);
            }

            /**
             * @return list<string>
             */
            protected function getSourceFilePaths(): array
            {
                return [$this->testSourceFilePath];
            }
        };
    }

    protected function createCommandsProvider(): Closure
    {
        return function (): array {
            $this->providerCallCount++;

            return [new EmptyAllCachesConsole()];
        };
    }

    protected function createConsoleConfig(bool $isEnabled): ConsoleConfig
    {
        return new class ($isEnabled, $this->cacheFilePath) extends ConsoleConfig {
            public function __construct(
                protected bool $isCacheEnabled,
                protected string $cacheFilePath
            ) {
            }

            public function isConsoleCommandCacheEnabled(): bool
            {
                return $this->isCacheEnabled;
            }

            public function getConsoleCommandCacheFilePath(): string
            {
                return $this->cacheFilePath;
            }

            /**
             * @return list<string>
             */
            public function getCacheExcludedConsoleCommandNames(): array
            {
                return [ConsoleCommandCacheTest::EXCLUDED_COMMAND_NAME];
            }
        };
    }

    protected function setRequestedCommand(string $commandName): void
    {
        $_SERVER['argv'] = ['console', $commandName, '--quiet-meta'];
    }
}
