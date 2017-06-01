<?php

abstract class AbstractManager
{
	const NOT_KEY = 0;
	const PRIMARY_KEY = 1;
	const UNIQUE_KEY = 2;
	const SIMPLE_KEY = 3;

	const INTEGER_TYPE = 1;
	const TEXT_TYPE = 2;
	const BOOLEAN_TYPE = 3;
	const HASH_TYPE = 4;

	const ORDER_BY_DEFAULT = 0;

	/**
	 * @var mysqli
	 */
	protected $mysqli;

	/**
	 * @param mysqli $mysqli
	 */
	public function __construct(mysqli $mysqli) {
		$this->mysqli = $mysqli;
	}

	/**
	 * @return array
	 */
	abstract protected function getFieldsMap();

	/**
	 * @return string
	 */
	abstract protected function getTableName();

	/**
	 * @param array $data
	 *
	 * @return IEntity
	 */
	abstract protected function populate(array $data);

	/**
	 * @return array
	 */
	protected function getSortByMap() {
		return [];
	}

	/**
	 * @param array $fields
	 *
	 * @return IEntity|null
	 */
	protected function getOneByFields(array $fields) {
		return 0 != count($entities = $this->getAllByFields($fields, 1)) ? reset($entities) : null;
	}

	/**
	 * @param array $fields
	 * @param integer $sortBy
	 * @param integer $limit
	 *
	 * @return IEntity[]
	 */
	protected function getAllByFields(array $fields = [], $sortBy = self::ORDER_BY_DEFAULT, $limit = 100) {
		$escapeMap = $this->getEscapeMap();
		$fieldsMap = $this->getFieldsMap();
		$sortByMap = $this->getSortByMap();

		$whereAnd = ["1"];
		foreach (array_intersect_key($fields, $fieldsMap) as $key => $value) {
			list ($fieldType) = $fieldsMap[$key];
			array_push($whereAnd, $key . "=" . $escapeMap[$fieldType]($value));
		}

		$select = "*";
		foreach ($fieldsMap as $key => $value) {
			list ($fieldType) = $fieldsMap[$key];
			if (self::HASH_TYPE == $fieldType) {
				$select .= ", lcase(hex(" . $key . ")) `" . $key . "`";
			}
		}

		$entities = [];
		if (($result = $this->mysqli->query("select " . $select . "
				from " . $this->getTableName() . "
				where " . implode(" and ", $whereAnd) . "
				order by " . (!array_key_exists($sortBy, $sortByMap) ? "NULL" : $sortByMap[$sortBy]) . " 
				limit " . $limit))) {
			while (($data = $result->fetch_assoc())) {
				array_push($entities, $this->populate($data));
			}
			$result->close();
		}

		return $entities;
	}

	/**
	 * @param array $fields
	 * @param bool $isRemove
	 *
	 * @return integer
	 */
	protected function storeFields(array $fields, $isRemove = false) {
		$fieldsMap = $this->getFieldsMap();
		$escapeMap = $this->getEscapeMap();
		$fieldSet = [
			self::NOT_KEY => [],
			self::UNIQUE_KEY => [],
			self::PRIMARY_KEY => [],
			self::SIMPLE_KEY => [],
		];
		foreach (array_intersect_key($fields, $fieldsMap) as $fieldName => $fieldValue) {
			if (!is_null($fieldValue)) {
				list ($fieldType, $keyType) = $fieldsMap[$fieldName];
				array_push($fieldSet[$keyType], $fieldName . "=" . $escapeMap[$fieldType]($fieldValue));
			}
		}
		if ($isRemove) {
			$this->mysqli->query("delete from " . $this->getTableName() . "
				where " . implode(" and ", array_merge($fieldSet[self::UNIQUE_KEY], $fieldSet[self::PRIMARY_KEY])));
			$result = -1 * $this->mysqli->affected_rows;
		} elseif (0 != count($fieldSet[self::PRIMARY_KEY])) {
			$this->mysqli->query("update " . $this->getTableName() . "
				set " . implode(", ", array_merge($fieldSet[self::UNIQUE_KEY], $fieldSet[self::SIMPLE_KEY], $fieldSet[self::NOT_KEY])) . "
				where " . implode(" and ", $fieldSet[self::PRIMARY_KEY]));
			$result = -1 * $this->mysqli->affected_rows;
		} else {
			$this->mysqli->query("insert " . $this->getTableName() . "
				set " . implode(", ", array_merge($fieldSet[self::UNIQUE_KEY], $fieldSet[self::SIMPLE_KEY], $fieldSet[self::NOT_KEY])) . "
				on duplicate key update " . implode(", ", array_merge($fieldSet[self::SIMPLE_KEY], $fieldSet[self::NOT_KEY])));
			$result = $this->mysqli->insert_id;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getEscapeMap() {
		return [
			self::INTEGER_TYPE => function ($number) {
				return $number;
			},
			self::TEXT_TYPE => function ($text) {
				return "'" . $this->mysqli->escape_string($text) . "'";
			},
			self::BOOLEAN_TYPE => function ($boolean) {
				return ($boolean ? "1" : "0");
			},
			self::HASH_TYPE => function ($hash) {
				return "0x" . $hash;
			},
		];
	}
}