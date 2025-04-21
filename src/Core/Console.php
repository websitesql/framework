<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Core;

use WebsiteSQL\Framework\Console\Command;

class Console
{
    /**
     * Command handler instance
     *
     * @var Command
     */
    protected Command $command;

    /**
     * Constructor
	 * 
	 * @param array $argv Command line arguments
     */
    public function __construct(array $argv = [])
    {
        $this->command = new Command();

        // Get the command
        $command = $argv[1] ?? 'help';
        
        // Get additional arguments
        $commandArgs = array_slice($argv, 2);

        // Run the command
        $this->command->run($command, $commandArgs);
    }
}