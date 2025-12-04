# Console Module
[![Latest Stable Version](https://poser.pugx.org/spryker/console/v/stable.svg)](https://packagist.org/packages/spryker/console)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

Console is a wrapper over Symfony's Console component, that makes the implementation and configuration of a console command easier. A console command is a php class that contains the implementation of a functionality that can get executed from the command line.

## Installation

```
composer require spryker/console
```

## Documentation

[Spryker Documentation](https://docs.spryker.com)

## Testing Console Commands

The Console module provides a `ConsoleHelper` to simplify testing console commands using Codeception.

### Using ConsoleHelper

The `ConsoleHelper` wraps your console command in a `CommandTester` instance, which allows you to execute the command programmatically and inspect its output and exit code.

#### Basic Usage

```php
use Codeception\Test\Unit;
use Symfony\Component\Console\Tester\CommandTester;
use Spryker\Zed\YourModule\Communication\Plugin\Console\YourConsoleCommand;

class YourConsoleCommandTest extends Unit
{
    protected YourModuleTester $tester;

    public function testExecutesSuccessfully(): void
    {
        // Arrange
        $command = new YourConsoleCommand();
        $commandTester = $this->tester->getConsoleTester($command);

        // Act
        $commandTester->execute([]);

        // Assert
        $this->assertSame(YourConsoleCommand::CODE_SUCCESS, $commandTester->getStatusCode());
        $this->assertStringContainsString('Expected output', $commandTester->getDisplay());
    }
}
```

#### Passing Arguments and Options

The `execute()` method accepts an array of input parameters where:
- **Arguments** are passed by name: `['argumentName' => 'value']`
- **Options** are passed with the `--` prefix: `['--option-name' => 'value']`

```php
public function testExecutesWithArguments(): void
{
    // Arrange
    $command = new YourConsoleCommand();
    $commandTester = $this->tester->getConsoleTester($command);

    // Act
    $commandTester->execute([
        'entityId' => 123,
        '--format' => 'json',
        '--verbose' => true,
    ]);

    // Assert
    $this->assertSame(YourConsoleCommand::CODE_SUCCESS, $commandTester->getStatusCode());
}
```

#### Execution Options

The `execute()` method accepts a second parameter for execution options:

```php
$commandTester->execute(
    ['--option' => 'value'],
    [
        'interactive' => false,
        'decorated' => false,
        'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        'capture_stderr_separately' => true,
    ]
);
```

Available execution options:
- `interactive`: Sets the input interactive flag
- `decorated`: Sets the output decorated flag
- `verbosity`: Sets the output verbosity level
- `capture_stderr_separately`: Make output of standard output and standard error separately available

#### Inspecting Command Output

After execution, you can inspect the command results:

```php
$commandTester->execute([]);

$exitCode = $commandTester->getStatusCode();
$output = $commandTester->getDisplay();
$input = $commandTester->getInput();
```

#### Testing Exception Cases

```php
public function testThrowsExceptionWhenFileNotFound(): void
{
    // Expect
    $this->expectException(FileNotFoundException::class);

    // Act
    $command = new YourConsoleCommand();
    $commandTester = $this->tester->getConsoleTester($command);
    $commandTester->execute([
        '--file' => 'invalid-file-path',
    ]);
}
```

### Helper Configuration

Ensure the `ConsoleHelper` is configured in your `codeception.yml`:

```yaml
modules:
    enabled:
        - \SprykerTest\Shared\Console\Helper\ConsoleHelper
```
