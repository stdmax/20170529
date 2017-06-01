<?php

abstract class AbstractEntity implements IEntity
{
	/**
	 * @return array
	 */
	public function getArray() {
		return get_object_vars($this);
	}
}