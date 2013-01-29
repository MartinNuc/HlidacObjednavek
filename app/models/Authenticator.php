<?php
use Nette\Utils\Strings;

class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{
	/** @var Nette\Database\Connection */
        private $database;

	public function __construct(DibiConnection $database)
        {
        $this->database = $database;
        }

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = dibi::select('*')->from('users')->where('username = %s', $username)->fetch();
                //$row = dibi::query("SELECT * FROM users WHERE username = %s", $username)->setRowClass('User');
                //$row = $this->database->table('users')->where('username', $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException("Špatná kombinace uživatelského jména a hesla.", self::IDENTITY_NOT_FOUND);
		}
                echo $this->calculateHash($password, $username);
		if ($row->password !== $this->calculateHash($password, $username) || $row->disabled==1) {
			throw new Nette\Security\AuthenticationException("Špatná kombinace uživatelského jména a hesla.", self::INVALID_CREDENTIAL);
		}

		unset($row->password);
                if ($row->role == "")
                    $row->role = "user";
                if ($row->role == "Administrátor")
                    $row->role = "admin";
                if ($row->role == "Host")
                    $row->role = "host";
		return new Nette\Security\Identity($row->id_user, $row->role, $row->toArray());
	}


        /**
         *
         * Gets username by his id.
         * 
         * @param int $id id of user we want to know
         * @return string username
         */
        public static function getUsernameById($id)
        {
            return dibi::select('username')->from('users')->where('id_user=%i', $id)->fetchSingle();
        }

	/**
	 * Computes salted password hash.
	 * @param  string password
	 * @return string hash salted by username
	 */
	public static function calculateHash($password, $username)
	{
		return md5($password . '765myPowerfullSalt<<!! :-) but they are quite useless anyway' . $username);
	}

}
