<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Providers;

use WebsiteSQL\Framework\Exceptions\NotFoundException;

use WebsiteSQL\Framework\Exceptions\IncorrectPasswordException;
use WebsiteSQL\Framework\Exceptions\UserNotApprovedException;
use WebsiteSQL\Framework\Exceptions\UserLockedOutException;
use WebsiteSQL\Framework\Exceptions\EmailNotVerifiedException;
use WebsiteSQL\Framework\Exceptions\SessionExpiredException;
use WebsiteSQL\Framework\App;
use WebsiteSQL\Framework\Exceptions\MissingRequiredFieldsException;
use WebsiteSQL\Framework\Exceptions\UserNotFoundException;
use Exception;

class AuthProvider
{
    /*
     * This object holds the Medoo database connection
     * 
     * @var Medoo
     */
    private App $app;

    /*
     * Constructor
     * 
     * @param string $realm
     * @param Medoo $database
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

	/* ------------------------------ *
	 * Token functions                *
	 * ------------------------------ */

	/*
	 * This method generates a new token
	 * 
	 * @param int $userId
	 * @return array
	 */
	public function generateToken($userId): array
	{
		// Generate a token
		$token = $this->app->getUtilities()->randomString(128);

		// Create the expiration date
		$expiresAt = $this->app->getUtilities()->calculateExpiryDate(
			new \DateTime(),
			(int)$this->app->getConfig()->get('auth.max_age'),
			(int)$this->app->getConfig()->get('auth.refresh')
		);

		// Insert the token into the database
		$this->app->getDatabase()->insert($this->app->getStrings()->getTableTokens(), [
			'uuid' => $this->app->getUtilities()->generateUuid(4),
			'token' => $token,
			'user' => $userId,
			'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
			'created_at' => date('Y-m-d H:i:s')
		]);

		// Return the token
		return [
			'token' => $token,
			'expires_at' => $expiresAt
		];
	}

	/*
	 * This method renews an existing token
	 * 
	 * @param string $token
	 * @return array
	 */
	public function renewToken(string $token): array
	{
		// Get the token from the database
		$token_row = $this->app->getDatabase()->get($this->app->getStrings()->getTableTokens(), '*', ['token' => $token]);
		if (!$token_row)
		{
			throw new NotFoundException('The token does not exist in the database.');
		}

		// Calculate the new expiration date
		$expiresAt = $this->app->getUtilities()->calculateExpiryDate(
			new \DateTime($token_row['created_at']),
			(int)$this->app->getConfig()->get('auth.max_age'),
			(int)$this->app->getConfig()->get('auth.refresh')
		);

		// Check if the new expiration date is in the past
		if ($expiresAt < new \DateTime())
		{
			throw new SessionExpiredException();
		}

		// Update the token timestamp
		$this->app->getDatabase()->update($this->app->getStrings()->getTableTokens(), [
			'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
		], ['id' => $token_row['id']]);

		// Return the token
		return [
			'token' => $token,
			'expires_at' => $expiresAt
		];
	}

	/*
	 * This method confirms the token is valid
	 * 
	 * @param string $token
	 * @return bool
	 */
	public function confirmToken(string $token): bool
	{
		try {
            // Check if the token is set
			if (!$token)
			{
				throw new NotFoundException('The token has not been provided.');
			}

			// Get the token from the database
			$tokenData = $this->app->getDatabase()->get($this->app->getStrings()->getTableTokens(), '*', ['token' => $token]);
			if (!$tokenData)
			{
				throw new NotFoundException('The token does not exist in the database.');
			}

            // Check if the token is expired
            $timeExpiresAt = strtotime($tokenData['expires_at']);
            $timeNow = time();

            // If the token is expired, delete it from the database and return false
            if ($timeNow > $timeExpiresAt)
            {
				// Delete the token from the database
                $this->app->getDatabase()->delete($this->app->getStrings()->getTableTokens(), ['token' => $token]);

				// Throw an exception
                throw new SessionExpiredException();
            }
			
			// Return true
			return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
	}

	/*
	 * This method destroys the token
	 * 
	 * @param string $token
	 * @return bool
	 */
	public function destroyToken(string $token): bool
	{
		try {
			// Get the token from the database
			$token_row = $this->app->getDatabase()->get($this->app->getStrings()->getTableTokens(), '*', ['token' => $token]);
			if (!$token_row)
			{
				throw new NotFoundException('The token does not exist in the database.');
			}

			// Delete the token from the database
			$this->app->getDatabase()->delete($this->app->getStrings()->getTableTokens(), ['token' => $token]);

			// Return the user's ID
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/* ------------------------------ *
	 * Authentication functions 	  *
	 * ------------------------------ */

    /* 
     * This method authenticates the user
     * 
     * @param string $email
     * @param string $password
	 * @param string $otp (optional)
     * @return array
     */
    public function authenticate(string $email, string $password, string $otp = null): array
	{
		// Check if the email and password are empty
		if (!$email || !$password)
		{
			throw new MissingRequiredFieldsException();
		}

		// Check if the email exists in the database
		$user_row = $this->app->getDatabase()->get($this->app->getStrings()->getTableUsers(), '*', ['email' => $email]);
		if (!$user_row)
		{
			throw new UserNotFoundException();
		}

		// Check if the password is correct
		if (!password_verify($password, $user_row['password']))
		{
			throw new IncorrectPasswordException();
		}
		
		// Check the user's email verification status
		if ($user_row['email_verified'] != 1)
		{
			throw new EmailNotVerifiedException();
		}

		// Check if user is locked out
		if ($user_row['status'] === 'locked')
		{
			throw new UserLockedOutException();
		}

		// Return the token
		return $user_row;
	}

	/* ------------------------------ *
	 * User functions                 *
	 * ------------------------------ */

	/*
	 * This method returns the user's ID from the token
	 * 
	 * @param string $token
	 * @return int|null
	 */
	public function getUserId(string $token): int|null
	{
		// Get the user's ID from the database
		$token_row = $this->app->getDatabase()->get($this->app->getStrings()->getTableTokens(), '*', ['token' => $token]);
		if (!$token_row)
		{
			return null;
		}

		// Return the user's ID
		return $token_row['user'];
	}
}