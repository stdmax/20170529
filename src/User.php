<?php

class User extends AbstractEntity
{
	/**
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * @var string|null
	 */
	protected $login = null;

	/**
	 * @return integer
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
	 * @return string|null
	 */
	function getLogin() {
		return $this->login;
	}

	/**
	 * @param string|null $login
	 */
	function setLogin($login) {
		$this->login = $login;
	}
}