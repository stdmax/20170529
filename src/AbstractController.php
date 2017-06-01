<?php

abstract class AbstractController
{
	/**
	 * @var UserManager
	 */
	protected $userManager;

	/**
	 * @var array
	 */
	protected $conf;

	/**
	 * AbstractController constructor.
	 *
	 * @param UserManager $userManager
	 * @param array $conf
	 */
	public function __construct(UserManager $userManager, array $conf) {
		$this->userManager = $userManager;
		$this->conf = $conf;
	}

	/**
	 * @param array $request
	 *
	 * @return boolean
	 */
	abstract public function mainAction(array $request);

	/**
	 * @return null|User
	 */
	public function getAuthorizedUser() {
		return $this->userManager->getAuthorizedUser();
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function authorizeUser(User $user) {
		return $this->userManager->authorizeUser($user);
	}

	/**
	 * @return bool
	 */
	public function unAuthorizeUser() {
		return $this->userManager->unAuthorizeUser();
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function redirect($url) {
		header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $url);

		return true;
	}

	/**
	 * @param array $request
	 * @param string $name
	 * @param integer $defaultValue
	 *
	 * @return integer
	 */
	protected function getNumberFromRequest(array $request, $name, $defaultValue = 0) {
		if (!array_key_exists($name, $request)
			|| !preg_match('/^[1-9]\d*|0$/', $number = strval($request[$name]))) {
			$number = $defaultValue;
		} else {
			$number = intval($number);
		}

		return $number;
	}

	/**
	 * @param array $request
	 * @param string $name
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	protected function getTextFromRequest(array $request, $name, $defaultValue = '') {
		if (!array_key_exists($name, $request)
			|| !is_string($text = strval($request[$name]))) {
			$text = $defaultValue;
		}

		return $text;
	}

	/**
	 * @param $data
	 * @param $method
	 *
	 * @return bool
	 */
	protected function renderJson($data, $action) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->prepareData($data + [
			'user' => $this->getAuthorizedUser(),
			'action' => $action,
		]));

		return true;
	}

	/**
	 * @param array|object $data
	 *
	 * @return array
	 */
	private function prepareData($data) {
		if (is_object($data)) {
			if ($data instanceof AbstractEntity) {
				$data = $data->getArray();
			}
		}
		if (is_array($data)) {
			foreach ($data as $name => $value) {
				$data[$name] = $this->prepareData($value);
			}
		}

		return $data;
	}
}
