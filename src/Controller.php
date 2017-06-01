<?php

class Controller extends AbstractController
{
	/**
	 * @var TodoManager
	 */
	protected $todoManager;

	/**
	 * Controller constructor.
	 *
	 * @param TodoManager $todoManager
	 * @param UserManager $userManager
	 * @param array $conf
	 */
	public function __construct(TodoManager $todoManager, UserManager $userManager, array $conf) {
		$this->todoManager = $todoManager;
		parent::__construct($userManager, $conf);
	}

	/**
	 * @inheritdoc
	 */
	public function mainAction(array $request) {
		return $this->itemsAction($request);
	}

	/**
	 * @param array $request
	 *
	 * @return bool
	 */
	public function registerAction(array $request) {
		if ($this->getAuthorizedUser()) {
			$response = $this->mainAction($request);
		} else {
			$data = [];
			foreach (['login', 'password'] as $name) {
				$data[$name] = $this->getTextFromRequest($request, $name, '');
			}
			if (0 != count(array_keys($data, '', true))) {
				$response = $this->renderJson($data, 'register');
			} elseif (!$this->userManager->checkLoginIsValid($data['login'])) {
				$data['message'] = 'Неправильный формат логина';
				$response = $this->renderJson($data, 'register');
			} elseif (!$this->userManager->checkPasswordIsValid($data['password'])) {
				$data['message'] = 'Неправильный формат пароля';
				$response = $this->renderJson($data, 'register');
			} elseif (!$this->userManager->checkLoginIsExist($data['login'])) {
				$data['message'] = 'Пользователь с таким логином уже есть';
				$response = $this->renderJson($data, 'register');
			} elseif (!is_null($user = $this->userManager->register($data['login'], $data['password']))) {
				$this->authorizeUser($user);
				$response = $this->mainAction([]);
			} else {
				$data['message'] = 'Ошибка';
				$response = $this->renderJson($data, 'register');
			}
		}

		return $response;
	}

	/**
	 * @param array $request
	 *
	 * @return bool
	 */
	public function loginAction(array $request) {
		if ($this->getAuthorizedUser()) {
			$response = $this->mainAction($request);
		} else {
			$data = [];
			foreach (['login', 'password'] as $name) {
				$data[$name] = $this->getTextFromRequest($request, $name, '');
			}
			if (0 != count(array_keys($data, '', true))) {
				$response = $this->renderJson($data, 'login');
			} elseif (!is_null($user = $this->userManager->getByLoginAndPassword($data['login'], $data['password']))) {
				$this->authorizeUser($user);
				$response = $this->mainAction([]);
			} else {
				$data['message'] = 'Пользователь с логином "' . htmlspecialchars($data['login']) . '" и указанным паролем не найден';
				$response = $this->renderJson($data, 'login');
			}
		}

		return $response;
	}

	/**
	 * @param array $request
	 *
	 * @return bool
	 */
	public function logoutAction(array $request) {
		if (!$this->getAuthorizedUser()) {
			$response = $this->loginAction([]);
		} else {
			$this->unAuthorizeUser();
			$response = $this->mainAction([]);
		}

		return $response;
	}

	/**
	 * @param array $request
	 *
	 * @return bool
	 */
	public function itemsAction(array $request) {
		if (!($user = $this->getAuthorizedUser())) {
			$response = $this->loginAction($request);
		} else {
			$data = [
				'items' => $this->todoManager->getAllForUser($user),
			];
			$response = $this->renderJson($data, 'items');
		}

		return $response;
	}

	/**
	 * @param array $request
	 *
	 * @return bool
	 */
	public function storeAction(array $request) {
		if (!($user = $this->getAuthorizedUser())) {
			$response = $this->loginAction($request);
		} else {
			$process = [
				'store' => [],
				'remove' => [],
			];
			/** @var stdClass[] $items */
			$items = json_decode($this->getTextFromRequest($request, 'items', '{}'));
			foreach ($items as $item) {
				$todoItem = new TodoItem();
				if (isset($item->id)
					&& preg_match('/^[1-9]\d*$/', $item->id)) {
					$todoItem->setId(intval($item->id));
				}
				$todoItem->setUserId($user->getId());
				if (!empty($item->isRemoved)) {
					array_push($process['remove'], $todoItem);
				} elseif (isset($item->text)
					&& is_string($item->text)
					&& isset($item->hash)
					&& is_string($item->hash)
					&& preg_match('/^[\da-f]{32}$/', $item->hash)) {
					$todoItem->setHash($item->hash);
					$todoItem->setIsComplete(!empty($item->isComplete));
					$todoItem->setText($item->text);
					array_push($process['store'], $todoItem);
				}
			}
			foreach (['remove' => true, 'store' => false] as $type => $isRemove) {
				/** @var TodoItem $todoItem */
				foreach ($process[$type] as $todoItem) {
					$this->todoManager->store($todoItem, $isRemove);
				}
			}
			$data = [
				'skipShow' => true,
				'items' => $this->todoManager->getAllForUser($user),
			];
			$response = $this->renderJson($data, 'items');
		}

		return $response;
	}
}