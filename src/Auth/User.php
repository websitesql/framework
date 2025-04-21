<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Auth;

use WebsiteSQL\Framework\Exceptions\UserAlreadyExistsException;
use WebsiteSQL\Framework\Core\App;
use WebsiteSQL\Framework\Utilities\Utilities;

class User
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

    /*
     * This method registers a new user
     * 
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $password
     * @param bool $approved
     * @param bool $email_verified
     * @return bool
     */
    public function register(string $firstname, string $lastname, string $email, string $password, bool $approved = false, bool $email_verified = false): bool
    {
        // Check if the email is already in use
        $UserQuery = $this->app->getDatabase()->get('users', '*', ['email' => $email]);
        if ($UserQuery)
        {
            throw new UserAlreadyExistsException();
        }

        // Hash the password
        $password = password_hash($password, PASSWORD_ARGON2ID);

        // Insert the user into the database
        $this->app->getDatabase()->insert('users', [
            'uuid' => Utilities::uuid()->generate('4')->toString(),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,
            'approved' => $approved,
            'locked' => 0,
            'email_verified' => $email_verified,
            'created_at' => Utilities::datetime()->toString(),
        ]);

        // Get the user ID
        $id = $this->app->getDatabase()->id();

        // Send the confirmation email
        $this->sendConfirmationEmail((int) $id);

        return true;
    }

    /*
     * This method gets a user by their ID
     * 
     * @param int $id
     * @return array
     */
    public function getUserById(int $id): array
    {
        return $this->app->getDatabase()->get('users', '*', ['id' => $id]);
    }

    /*
     * This method gets all users
     * 
     * @return array
     */
    public function getUsers(): array
    {
        return $this->app->getDatabase()->select('users', '*');
    }

    /*
     * This method sends a user confirmation email
     * 
     * @param int $id
     * @return bool
     */
    public function sendConfirmationEmail(int $id): bool
    {
        $user = $this->getUserById($id);

        if (!$user) {
            return false;
        }

        // Generate a token for the email confirmation link
        $token = Utilities::string()->random(128);
        $expiry = new \DateTime('+1 day');

        // Update the user with the token and expiry
        $this->app->getDatabase()->update('users', [
            'email_verify_code' => $token,
            'email_verify_expiry' => $expiry->format('Y-m-d H:i:s')
        ], ['id' => $id]);

		// Send the confirmation email
		$this->app->getMailer()->template('email-confirmation', [
			'firstname' => $user['firstname'],
			'token' => $token,
			'expiry' => $expiry->format('Y-m-d H:i:s')
		])->subject('Verify your email address')->send($user['email']);

        return true;
    }
}