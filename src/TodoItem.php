<?php

class TodoItem extends AbstractEntity
{
	/**
	 * @var integer|null
	 */
	protected $id;

	/**
	 * @var integer|null
	 */
	protected $userId = null;

	/**
	 * @var string|null
	 */
	protected $hash = null;

	/**
	 * @var boolean
	 */
	protected $isComplete;

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @return integer|null
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * @param integer $id
	 */
	function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return integer
	 */
	function getUserId() {
		return $this->userId;
	}

	/**
	 * @param integer $userId
	 */
	function setUserId($userId) {
		$this->userId = $userId;
	}

	/**
	 * @return string
	 */
	function getHash() {
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	function setHash($hash) {
		$this->hash = $hash;
	}

	/**
	 * @return boolean
	 */
	function getIsComplete() {
		return $this->isComplete;
	}

	/**
	 * @param boolean $isComplete
	 */
	function setIsComplete($isComplete) {
		$this->isComplete = $isComplete;
	}

	/**
	 * @return string
	 */
	function getText() {
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	function setText($text) {
		$this->text = $text;
	}
}