<?php

class TodoManager extends AbstractManager
{
	/**
	 * TodoManager constructor.
	 *
	 * @param mysqli $mysqli
	 */
	public function __construct(mysqli $mysqli) {
		parent::__construct($mysqli);
	}

	/**
	 * @param User $user
	 *
	 * @return TodoItem[]
	 */
	public function getAllForUser(User $user) {
		/* @var $todoItems TodoItem[] */
		$todoItems = $this->getAllByFields([
			'user_id' => $user->getId(),
		]);

		return $todoItems;
	}

	/**
	 * @param TodoItem $todoItem
	 * @param boolean $isRemove
	 *
	 * @return integer
	 */
	public function store(TodoItem $todoItem, $isRemove = false) {
		return $this->storeFields([
			'id' => $todoItem->getId(),
			'user_id' => $todoItem->getUserId(),
			'hash' => $todoItem->getHash(),
			'is_complete' => $todoItem->getIsComplete() ? '1' : '0',
			'text' => $todoItem->getText()
		], $isRemove);
	}

	/**
	 * @inheritdoc
	 */
	protected function getFieldsMap() {
		return [
			'id' => [self::INTEGER_TYPE, self::PRIMARY_KEY],
			'user_id' => [self::INTEGER_TYPE, self::UNIQUE_KEY],
			'hash' => [self::HASH_TYPE, self::UNIQUE_KEY],
			'is_complete' => [self::BOOLEAN_TYPE, self::NOT_KEY],
			'text' => [self::TEXT_TYPE, self::NOT_KEY],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getSortByMap() {
		return [
			self::ORDER_BY_DEFAULT => 'id asc',
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getTableName() {
		return 'todos';
	}

	/**
	 * @param array $data
	 *
	 * @return TodoItem
	 */
	protected function populate(array $data) {
		$todoItem = new TodoItem();
		$todoItem->setId(intval($data['id']));
		$todoItem->setUserId($data['user_id']);
		$todoItem->setHash($data['hash']);
		$todoItem->setIsComplete(!empty($data['is_complete']));
		$todoItem->setText($data['text']);

		return $todoItem;
	}
}