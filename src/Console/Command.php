<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Console;

class Command
{
	/**
	 * Available commands
	 * 
	 * @var array
	 */
	protected array $commands = [];

	/**
	 * Command namespace
	 * 
	 * @var string
	 */
	protected string $commandNamespace = 'WebsiteSQL\\Framework\\Console\\Commands\\';
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->registerCommands();
	}

	/**
	 * Register built-in commands
	 * 
	 * @return void
	 */
	protected function registerCommands(): void
	{
		$this->commands = [
			'help' => 'Display help information',
			'version' => 'Display framework version',
			'migration:run' => 'Run all pending migrations',
			'migration:rollback' => 'Roll back the last batch of migrations',
			'migration:create' => 'Create a new migration',
			'api:create' => 'Create a new API endpoint',
		];
	}

	/**
	 * This method runs the command line interface
	 * 
	 * @param string $command
	 * @param array $args
	 * @return void
	 */
	public function run(string $command, array $args = []): void
	{
		// Check if the command is valid
		if (!array_key_exists($command, $this->getCommands())) {
			echo "Invalid command: $command\n";
			echo "Run 'websitesql help' to see available commands\n";
			return;
		}

		// Run the command
		if (in_array($command, ['help', 'version'])) {
			// Handle built-in commands
			$this->$command($args);
		} else {
			// Handle commands from the Commands directory
			$this->executeCommand($command, $args);
		}
	}

	/**
	 * Execute a command from the Commands directory
	 * 
	 * @param string $command
	 * @param array $args
	 * @return void
	 */
	protected function executeCommand(string $command, array $args): void
	{
		// Convert command string to class name (e.g., migration:run -> MigrationRun)
		$className = str_replace(':', '', ucwords($command, ':'));
		$fullyQualifiedClassName = $this->commandNamespace . $className;

		// Check if the command class exists
		if (class_exists($fullyQualifiedClassName)) {
			$commandInstance = new $fullyQualifiedClassName();
			if (method_exists($commandInstance, 'handle')) {
				$commandInstance->handle($args);
			} else {
				echo "Command class {$className} does not have a handle method\n";
			}
		} else {
			echo "Command class {$className} not found\n";
		}
	}

	/**
	 * Get available commands
	 * 
	 * @return array
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Display help information
	 * 
	 * @param array $args
	 * @return void
	 */
	public function help(array $args = []): void
	{
		echo "WebsiteSQL Framework CLI\n\n";
		echo "Usage: websitesql [command] [options]\n\n";
		echo "Available commands:\n";
		
		foreach ($this->getCommands() as $command => $description) {
			echo "  $command" . str_repeat(' ', 20 - strlen($command)) . "$description\n";
		}
		
		echo "\nFor more information about a command, run 'websitesql [command] --help'\n";
	}

	/**
	 * This method returns the version of the application
	 * 
	 * @return string
	 */
	public function version(): string
	{
		$version = 'WebsiteSQL Framework v1.0.0';
		echo $version . "\n";
		return $version;
	}
}