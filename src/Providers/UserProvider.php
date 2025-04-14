<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Providers;

use WebsiteSQL\Framework\Exceptions\UserAlreadyExistsException;
use WebsiteSQL\Framework\App;

class UserProvider
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
        $UserQuery = $this->app->getDatabase()->get($this->app->getStrings()->getTableUsers(), '*', ['email' => $email]);
        if ($UserQuery)
        {
            throw new UserAlreadyExistsException();
        }

        // Hash the password
        $password = password_hash($password, PASSWORD_ARGON2ID);

        // Insert the user into the database
        $this->app->getDatabase()->insert($this->app->getStrings()->getTableUsers(), [
            'uuid' => $this->app->getUtilities()->generateUuid(4),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,
            'approved' => $approved,
            'locked' => 0,
            'email_verified' => $email_verified,
            'created_at' => $this->app->getUtilities()->getDateTime(),
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
        return $this->app->getDatabase()->get($this->app->getStrings()->getTableUsers(), '*', ['id' => $id]);
    }

    /*
     * This method gets all users
     * 
     * @return array
     */
    public function getUsers(): array
    {
        return $this->app->getDatabase()->select($this->app->getStrings()->getTableUsers(), '*');
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
        $token = $this->app->getUtilities()->randomString(100);
        $expiry = new \DateTime('+1 day');

        // Update the user with the token and expiry
        $this->app->getDatabase()->update($this->app->getStrings()->getTableUsers(), [
            'email_verify_code' => $token,
            'email_verify_expiry' => $expiry->format('Y-m-d H:i:s')
        ], ['id' => $id]);

        // Generate the email content
        $content = $this->app->getRenderer()->render('email::email-confirmation', [
            'firstname' => $user['firstname'],
            'url' => $this->app->getRouter()->getRoute('app.confirm-email', ['token' => $token])
        ]);

        // Send the email
        $this->app->getMail()->send($user['email'], 'Verify your email address', $content);

        return true;
    }
}