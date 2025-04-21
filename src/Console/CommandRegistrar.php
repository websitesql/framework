<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Console;

class CommandRegistrar
{
    /**
     * The command instance
     */
    protected Command $command;
    
    /**
     * The command name
     */
    protected string $commandName;
    
    /**
     * Constructor
     */
    public function __construct(Command $command, string $commandName)
    {
        $this->command = $command;
        $this->commandName = $commandName;
    }
    
    /**
     * Set the description for the command
     * 
     * @param string $description
     * @return $this
     */
    public function description(string $description): self
    {
        $this->command->setCommandDescription($this->commandName, $description);
        return $this;
    }
}
