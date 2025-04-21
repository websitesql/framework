<?php declare(strict_types=1);

namespace WebsiteSQL\Framework;

use WebsiteSQL\Framework\Config\Config;
use WebsiteSQL\Framework\Mailer\Mailer;
use WebsiteSQL\Framework\Database\Database;

class Kernel
{
	/**
	 * This string holds the version of the kernel
	 * 
	 * @var string
	 */
	public const VERSION = '1.0.2';

    /**
     * This string holds the basePath for the application
     * 
     * @var string
     */
    private $basePath;

	/**
     * This object holds the Config class
     * 
     * @var Config
     */
    private Config $config;

	/**
	 * This object holds the Database class
	 * 
	 * @var Database
	 */
	private Database $database;

	/**
	 * This object holds the Mailer class
	 * 
	 * @var Mailer
	 */
	private Mailer $mailer;

    /*
     * Constructor
     */
    public function __construct()
    {
        // Determine the project root using Composer's autoloader
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $vendorDir = dirname(dirname($reflection->getFileName()));
        $this->basePath = dirname($vendorDir);

        // Load the config
        $this->config = new Config($this->basePath);

        // Set timezone
        date_default_timezone_set($this->config->get('app.timezone'));

		// Connect to the database
		$this->database = new Database([
			'type' => $this->config->get('database.driver'),
			'host' => $this->config->get('database.host'),
			'database' => $this->config->get('database.name'),
			'username' => $this->config->get('database.username'),
			'password' => $this->config->get('database.password')
		], '../migrations');

		// Load the mail provider
		$this->mailer = new Mailer([
			'driver' => $this->config->get('mail.driver'), // Mail driver (smtp, sendmail, mailgun, etc.)
			'from' => $this->config->get('mail.from'), // Sender email address
			'from_name' => $this->config->get('app.name'), // Sender name
			'template_path' => $this->config->get('path.mailer'), // Path to email templates
			'smtp_host' => $this->config->get('mail.smtp_host'), // SMTP server address
			'smtp_port' => $this->config->get('mail.smtp_port'), // SMTP server port
			'smtp_username' => $this->config->get('mail.smtp_user'), // SMTP username
			'smtp_password' => $this->config->get('mail.smtp_pass'), // SMTP password
			'debug' => $this->config->get('app.debug'), // Enable debug mode
		]);
    }

	/**
	 * This method returns the config object
	 * 
	 * @return Config
	 */
	public function getConfig(): Config
	{
		// Return the config object
		return $this->config;
	}

	/**
	 * This method returns the database object
	 * 
	 * @return Database
	 */
	public function getDatabase(): Database
	{
		// Return the database object
		return $this->database;
	}

    /*
     * This method returns the basePath string
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        // Return the basePath string
        return $this->basePath;
    }

	/**
	 * This method returns the mail object
	 * 
	 * @return Mailer
	 */
	public function getMailer(): Mailer
	{
		// Return the mail object
		return $this->mailer;
	}
}