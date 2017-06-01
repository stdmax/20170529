<?php

class UserManager extends AbstractManager
{
	/**
	 * @var array
	 */
	protected $session;

	/**
	 * UserManager constructor.
	 *
	 * @param mysqli $mysqli
	 * @param array $session
	 */
	public function __construct(mysqli $mysqli, array &$session) {
		$this->session = &$session;
		parent::__construct($mysqli);
	}

	/**
	 * @return null|User
	 */
	public function getAuthorizedUser() {
		if (!array_key_exists($key = 'userLogin', $this->session)
			|| !($user = $this->getByLogin($this->session[$key]))) {
			$user = null;
		}

		return $user;
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function authorizeUser(User $user) {
		$this->session['userLogin'] = $user->getLogin();

		return true;
	}

	/**
	 * @return bool
	 */
	public function unAuthorizeUser() {
		if (array_key_exists($key = 'userLogin', $this->session)) {
			unset($this->session[$key]);
		}

		return true;
	}

	/**
	 * @param string $login
	 *
	 * @return boolean
	 */
	public function checkLoginIsExist($login) {
		return is_null($this->getByLogin($login));
	}

	/**
	 * @param string $login
	 *
	 * @return boolean
	 */
	public function checkLoginIsValid($login) {
		return 2 < strlen($login);
	}

	/**
	 * @param string $password
	 *
	 * @return boolean
	 */
	public function checkPasswordIsValid($password) {
		return 2 < strlen($password);
	}

	/**
	 * @param string $login
	 * @param string $password
	 *
	 * @return null|User
	 */
	public function register($login, $password) {
		$userId = $this->storeFields([
			'login' => $login,
			'password' => $this->getPasswordHash($password),
		]);

		return $this->getById($userId);
	}

	/**
	 * @param string $id
	 *
	 * @return User|null
	 */
	public function getById($id) {
		/* @var $user User */
		$user = $this->getOneByFields([
			'id' => $id,
		]);

		return $user;
	}

	/**
	 * @param string $login
	 *
	 * @return User|null
	 */
	public function getByLogin($login) {
		/* @var $user User */
		$user = $this->getOneByFields([
			'login' => $login,
		]);

		return $user;
	}

	/**
	 * @param string $login
	 * @param string $password
	 *
	 * @return User|null
	 */
	public function getByLoginAndPassword($login, $password) {
		/* @var $user User */
		$user = $this->getOneByFields([
			'login' => $login,
			'password' => $this->getPasswordHash($password),
		]);

		return $user;
	}

	/**
	 * @inheritdoc
	 */
	protected function getFieldsMap() {
		return [
			'id' => [self::INTEGER_TYPE, self::PRIMARY_KEY],
			'login' => [self::TEXT_TYPE, self::NOT_KEY],
			'password' => [self::HASH_TYPE, self::NOT_KEY],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTableName() {
		return 'users';
	}

	/**
	 * @param string $password
	 *
	 * @return string
	 */
	protected function getPasswordHash($password) {
		return md5($password);
	}

	/**
	 * @param array $data
	 *
	 * @return User
	 */
	protected function populate(array $data) {
		$user = new User();
		$user->setId($data['id']);
		$user->setLogin($data['login']);

		return $user;
	}
}