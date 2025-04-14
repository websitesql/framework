<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Console\Commands;

class Auth
{
	/**
	 * This method creates a new user
	 * 
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $email
	 * @param string $password
	 * @return string
	 */
	public function createUser(string $firstname, string $lastname, string $email, string $password): string
	{
		

		return 'User created successfully';
	}
}