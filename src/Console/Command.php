<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Console;

class Command
{
	/**
	 * This string holds the version of the command
	 * 
	 * @var string
	 */
	public const VERSION = '1.0.0';

	/**
	 * All registered commands
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
	 * Command directory path
	 * 
	 * @var string
	 */
	protected string $commandDirectory;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set the command directory path
		$this->commandDirectory = dirname(__DIR__) . '/Console/Commands';
		
		// Register commands from the Commands directory
		$this->registerDirectoryCommands();
		
		// Load user-defined console routes
		$this->loadConsoleRoutes();
	}

	/**
	 * Register commands from the Commands directory
	 * 
	 * @return void
	 */
	protected function registerDirectoryCommands(): void
	{
		if (!is_dir($this->commandDirectory)) {
			return;
		}
		
		// Scan the Commands directory for command files
		$files = scandir($this->commandDirectory);
		foreach ($files as $file) {
			if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
				continue;
			}
			
			$className = pathinfo($file, PATHINFO_FILENAME);
			$fullyQualifiedClassName = $this->commandNamespace . $className;
			
			// Check if class exists and has required methods
			if (class_exists($fullyQualifiedClassName)) {
				$reflection = new \ReflectionClass($fullyQualifiedClassName);
				
				// Skip abstract classes
				if ($reflection->isAbstract()) {
					continue;
				}
				
				$commandInstance = new $fullyQualifiedClassName();
				
				// Get command name from the class
				// Convert CamelCase to kebab-case with colons (e.g., MigrationRun to migration:run)
				$commandName = $this->getCommandName($className);
				
				// Get command description if available
				$description = 'No description provided';
				if (method_exists($commandInstance, 'getDescription')) {
					$description = $commandInstance->getDescription();
				}
				
				// Register the command
				$this->commands[$commandName] = [
					'class' => $fullyQualifiedClassName,
					'description' => $description
				];
			}
		}
		
		// Add built-in version command
		$this->command('version', function() {
			$version = 'WebsiteSQL Framework v1.0.0';
			echo $version . "\n";
			return $version;
		})->description('Display framework version');
	}
	
	/**
	 * Convert class name to command name
	 * 
	 * @param string $className
	 * @return string
	 */
	protected function getCommandName(string $className): string
	{
		// Convert from CamelCase to kebab-case with colons
		// e.g., MigrationRun to migration:run
		$parts = preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);
		$parts = array_map('strtolower', $parts);
		
		// Special handling for known command patterns
		$result = implode(':', $parts);
		
		// Handle common prefixes like migration, make, etc.
		$prefixes = ['migration', 'api'];
		foreach ($prefixes as $prefix) {
			if (strpos($result, $prefix . ':') === 0) {
				// Already properly formatted
				return $result;
			}
		}
		
		// For commands that don't follow the prefix:action pattern, just return as-is
		return $result;
	}

	/**
	 * Load console routes from the routes file
	 * 
	 * @return void
	 */
	protected function loadConsoleRoutes(): void
	{
		$routesPath = getcwd() . '/routes/console.php';
		
		if (file_exists($routesPath)) {
			$app = $this;
			require $routesPath;
		}
	}

	/**
	 * Register a new console command
	 * 
	 * @param string $command
	 * @param callable $callback
	 * @return CommandRegistrar
	 */
	public function command(string $command, callable $callback): CommandRegistrar
	{
		$this->commands[$command] = [
			'callback' => $callback,
			'description' => ''
		];
		
		return new CommandRegistrar($this, $command);
	}

	/**
	 * Set the description for a command
	 * 
	 * @param string $command
	 * @param string $description
	 * @return void
	 */
	public function setCommandDescription(string $command, string $description): void
	{
		if (isset($this->commands[$command])) {
			$this->commands[$command]['description'] = $description;
		}
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
		// Special case for help command
		if ($command === 'help') {
			$this->help($args);
			return;
		}
		
		// Check if the command exists
		if (!isset($this->commands[$command])) {
			echo "Invalid command: $command\n";
			echo "Run 'websitesql help' to see available commands\n";
			return;
		}

		// Run the command
		$commandInfo = $this->commands[$command];
		
		if (isset($commandInfo['callback'])) {
			// Run callable command
			call_user_func($commandInfo['callback'], $args);
		} else if (isset($commandInfo['class'])) {
			// Run class-based command
			$commandInstance = new $commandInfo['class']();
			if (method_exists($commandInstance, 'handle')) {
				$commandInstance->handle($args);
			} else {
				echo "Command class does not have a handle method\n";
			}
		}
	}

	/**
	 * Get available commands
	 * 
	 * @return array
	 */
	public function getCommands(): array
	{
		$commandList = [];
		
		// Format commands for display
		foreach ($this->commands as $command => $details) {
			$description = $details['description'] ?? 'No description provided';
			$commandList[$command] = $description;
		}
		
		// Add help command
		$commandList['help'] = 'Display help information';
		
		return $commandList;
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
}