<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Console\Business\CommandCache;

use Closure;
use ReflectionClass;
use Spryker\Zed\Console\ConsoleConfig;
use Spryker\Zed\Console\ConsoleDependencyProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;

class ConsoleCommandCache implements ConsoleCommandCacheInterface
{
    protected const string CACHE_KEY_SIGNATURE = 'signature';

    protected const string CACHE_KEY_MAP = 'map';

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $loadedCache = null;

    public function __construct(
        protected ConsoleConfig $consoleConfig,
        protected Closure $commandsProvider
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @return array<\Symfony\Component\Console\Command\Command>
     */
    public function getCommands(): array
    {
        $command = $this->findCommand();
        if ($command !== null) {
            return [$command];
        }

        $commands = ($this->commandsProvider)();
        $this->store($commands);

        return $commands;
    }

    protected function findCommand(): ?Command
    {
        if (!$this->consoleConfig->isConsoleCommandCacheEnabled()) {
            return null;
        }

        $commandName = $this->getRequestedCommandName();
        if ($commandName === null || in_array($commandName, $this->consoleConfig->getCacheExcludedConsoleCommandNames(), true)) {
            return null;
        }

        $commandMap = $this->getFreshCommandMap();
        if ($commandMap === null || !isset($commandMap[$commandName])) {
            return null;
        }

        $command = $this->instantiateCommand($commandMap[$commandName], $commandName);

        if ($command === null || $command->getName() !== $commandName) {
            return null;
        }

        return $command;
    }

    /**
     * @param array<\Symfony\Component\Console\Command\Command> $commands
     *
     * @return void
     */
    protected function store(array $commands): void
    {
        if (!$this->consoleConfig->isConsoleCommandCacheEnabled() || $this->isCacheFresh()) {
            return;
        }

        $commandMap = [];
        foreach ($commands as $command) {
            $commandName = $command->getName();
            if ($commandName === null || $commandName === '') {
                continue;
            }

            $commandMap[$commandName] = $command::class;
        }

        $this->writeCacheFile($commandMap);
    }

    protected function getRequestedCommandName(): ?string
    {
        $firstArgument = (new ArgvInput())->getFirstArgument();

        return ($firstArgument === null || $firstArgument === '') ? null : $firstArgument;
    }

    protected function instantiateCommand(string $commandClass, string $commandName): ?Command
    {
        if (!class_exists($commandClass)) {
            return null;
        }

        $command = new $commandClass($commandName);

        return $command instanceof Command ? $command : null;
    }

    /**
     * @return array<string, string>|null
     */
    protected function getFreshCommandMap(): ?array
    {
        if (!$this->isCacheFresh()) {
            return null;
        }

        $map = $this->loadCache()[static::CACHE_KEY_MAP] ?? null;

        return is_array($map) ? $map : null;
    }

    protected function isCacheFresh(): bool
    {
        if (!is_file($this->consoleConfig->getConsoleCommandCacheFilePath())) {
            return false;
        }

        return ($this->loadCache()[static::CACHE_KEY_SIGNATURE] ?? null) === $this->buildSignature();
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadCache(): array
    {
        if ($this->loadedCache !== null) {
            return $this->loadedCache;
        }

        $cacheFilePath = $this->consoleConfig->getConsoleCommandCacheFilePath();
        $cache = is_file($cacheFilePath) ? include $cacheFilePath : [];
        $this->loadedCache = is_array($cache) ? $cache : [];

        return $this->loadedCache;
    }

    protected function buildSignature(): string
    {
        $signatureParts = [];
        foreach ($this->getSourceFilePaths() as $sourceFilePath) {
            if (!is_file($sourceFilePath)) {
                continue;
            }

            $signatureParts[] = sprintf('%s:%d:%d', $sourceFilePath, (int)filemtime($sourceFilePath), (int)filesize($sourceFilePath));
        }

        return implode('|', $signatureParts);
    }

    /**
     * Resolves the files whose modification invalidates the cache: this module's ConsoleDependencyProvider
     * and any project-level child of it, since the command list is defined there. Nothing needs to be
     * declared at the project level.
     *
     * @return list<string>
     */
    protected function getSourceFilePaths(): array
    {
        $filePaths = [];
        foreach (get_declared_classes() as $className) {
            if (!is_a($className, ConsoleDependencyProvider::class, true)) {
                continue;
            }

            $fileName = (new ReflectionClass($className))->getFileName();
            if ($fileName !== false) {
                $filePaths[$fileName] = $fileName;
            }
        }

        return array_values($filePaths);
    }

    /**
     * @param array<string, string> $commandMap
     *
     * @return void
     */
    protected function writeCacheFile(array $commandMap): void
    {
        $cacheFilePath = $this->consoleConfig->getConsoleCommandCacheFilePath();
        $directory = dirname($cacheFilePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return;
        }

        $payload = [
            static::CACHE_KEY_SIGNATURE => $this->buildSignature(),
            static::CACHE_KEY_MAP => $commandMap,
        ];

        $content = sprintf('<?php%s%sreturn %s;%s', PHP_EOL, PHP_EOL, var_export($payload, true), PHP_EOL);

        $temporaryFilePath = sprintf('%s.%d.tmp', $cacheFilePath, getmypid());
        if (file_put_contents($temporaryFilePath, $content, LOCK_EX) === false) {
            return;
        }

        rename($temporaryFilePath, $cacheFilePath);
        $this->loadedCache = $payload;
    }
}
