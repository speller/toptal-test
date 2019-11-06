<?php
/**
 * Created by PhpStorm.
 * User: pravdin
 * Date: 29.04.2017
 * Time: 20:38
 */
namespace App\Db;

/**
 * Basic DB provider routines. Can be used as provider class parent or as standalone object
 */
class MySqlProviderHelper extends MySqlHelper {
	/**
	 * @var callable[]|null
	 */
	private $buildModelCallback;

	/**
	 * @param \PDO $connection
	 * @param null|string $tableName
	 * @param callable|callable[]|null $buildModelCallback
	 */
	public function __construct(\PDO $connection, $tableName = '', $buildModelCallback = null) {
		parent::__construct($connection, $tableName);
		if ($buildModelCallback && !is_array($buildModelCallback)) {
			$buildModelCallback = [$tableName => $buildModelCallback];
		}
		$this->buildModelCallback = $buildModelCallback;
	}

	/**
	 * Build model object from raw data
	 * @param object|null $data
	 * @param string|null $tableName
	 * @return null|object
	 */
	public function buildModelFromData($data, $tableName) {
		if (!is_null($data)) {
			if ($this->buildModelCallback) {
				return call_user_func_array($this->buildModelCallback[$tableName], [$data]);
			} else {
				$method = 'build' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
				if (method_exists($this, $method)) {
					return $this->$method($data);
				} else {
					return $data;
				}
			}
		} else {
			return null;
		}
	}

	/**
	 * Build models array from rows
	 * @param object[] $data
	 * @param string|null $tableName
	 * @return mixed[]
	 */
	public function buildModelsFromData(array $data, $tableName) {
		return array_map([$this, 'buildModelFromData'], $data, array_pad([], count($data), $tableName));
	}

	/**
	 * Delete row by id
	 * @param mixed $id
	 */
	public function deleteById($id) {
		$this->deleteById2($this->tableName, $id);
	}

	/**
	 * Delete row by id
	 * @param string $tableName
	 * @param mixed $id
	 */
	public function deleteById2($tableName, $id) {
		$this->exec(
			sprintf('DELETE FROM :table WHERE id %s :id', is_array($id) ? 'IN' : '='),
			['id' => $id],
			$tableName
		);
	}

	/**
	 * Удаление по заданному одному или нескольким условиям
	 * @param string|array $field
	 * @param mixed $value
	 * @param string $conditionOp
	 * @return mixed|null
	 */
	public function deleteBy($field, $value = null, $conditionOp = 'AND') {
		return $this->deleteBy2($this->tableName, $field, $value, $conditionOp);
	}

	/**
	 * Удаление по заданному одному или нескольким условиям
	 * @param string $tableName
	 * @param string|array $field
	 * @param mixed $value
	 * @param string $conditionOp
	 * @return mixed|null
	 */
	public function deleteBy2($tableName, $field, $value = null, $conditionOp = 'AND') {
		if (!is_array($field)) {
			$field = [$field => $value];
		}
		list($where, $values) = $this->buildSqlValuePairs($field, $conditionOp);
		$where = $where ? "WHERE $where" : '';
		$sql = "DELETE FROM :table $where";
		return $this->exec($sql, $values, $tableName);
	}

	/**
	 * Поиск по id
	 * @param mixed $id
	 * @return mixed
	 */
	public function findById($id) {
		return $this->findById2($this->tableName, $id);
	}

	/**
	 * Поиск по id
	 * @param string $tableName
	 * @param mixed $id
	 * @return mixed
	 */
	public function findById2($tableName, $id) {
		return
			$this->buildModelFromData(
				$this->queryFirstRow(
					'SELECT * FROM :table WHERE id = :id', ['id' => $id], $tableName
				),
				$tableName
			);
	}

	/**
	 * Поиск одного элемента по заданному одному или нескольким условиям
	 * @param string|array $field
	 * @param mixed $value
	 * @param string|null $orderBy
	 * @param string $conditionOp
	 * @return mixed|null
	 */
	public function findBy($field, $value = null, $orderBy = null, $conditionOp = 'AND') {
		return $this->findBy2($this->tableName, $field, $value, $orderBy, $conditionOp);
	}

	/**
	 * Поиск одного элемента по заданному одному или нескольким условиям
	 * @param string|null $tableName
	 * @param string|array $field
	 * @param mixed $value
	 * @param null $orderBy
	 * @param string $conditionOp
	 * @return mixed|null
	 */
	public function findBy2($tableName, $field, $value = null, $orderBy = null, $conditionOp = 'AND') {
		if (!is_array($field)) {
			$field = [$field => $value];
		} elseif ($orderBy === null) {
			$orderBy = $value;
		}
		list($where, $values) = $this->buildSqlValuePairs($field, $conditionOp);
		$where = $where ? "WHERE $where" : '';
		$sql = "SELECT * FROM `$tableName` $where";
		if ($orderBy) {
			$sql .= " ORDER BY " . $orderBy;
		}
		$sql .= ' LIMIT 0, 1';
		return $this->buildModelFromData($this->queryFirstRow($sql, $values, $tableName), $tableName);
	}

	/**
	 * Поиск всех строк по заданным критериям
	 * @param string|array $field
	 * @param mixed $value значение поля или порядок сортировки если в $field передан массив
	 * @param string|null $orderBy
	 * @param string $conditionOp
	 * @return array
	 */
	public function fetchBy($field, $value = null, $orderBy = null, $conditionOp = 'AND') {
		return $this->fetchBy2($this->tableName, $field, $value, $orderBy, $conditionOp);
	}

	/**
	 * Сборка SQL пар `поле` = `значение` с учетом массивов и null-ов.
	 * Для SQL обновления передать $operation = 'UPDATE', тогда не будет производиться установка 'IS NULL'
	 * @param array $fieldValues
	 * @param string $operation
	 * @return array [SQL, values]
	 */
	protected function buildSqlValuePairs(array $fieldValues, $operation) {
		if ($operation == 'UPDATE') {
			$separator = ', ';
			$useIsNull = false;
			$prefix = 'v_';
		} else {
			$separator = $operation;
			$useIsNull = true;
			$prefix = '';
		}
		$c = $this->getEscapeChar();
		$where = [];
		$rawValues = [];
		foreach ($fieldValues as $name => $value) {
			if (!is_array($value)) {
				if (preg_match('/^(\w+)\s*([><=]{1,2}|\sLIKE)$/', $name, $matches)) {
					$comparison = $matches[2];
					$name = trim($matches[1]);
				} else {
					$comparison = '=';
				}
				$where[] =
					($value !== null || !$useIsNull) ?
						"{$c}{$name}{$c} $comparison :{$prefix}{$name}" :
						"{$c}{$name}{$c} IS NULL";
			} else {
				$where[] = "{$c}{$name}{$c} IN :{$prefix}{$name}";
			}
			$rawValues[$prefix . $name] = $value;
		}
		return [
			$where ? implode(' ' . $separator . ' ', $where) : '',
			$rawValues
		];
	}

	/**
	 * Поиск всех строк по заданным критериям
	 * @param string|null $tableName
	 * @param string|array $field
	 * @param mixed $value значение поля или порядок сортировки если в $field передан массив
	 * @param string|null $orderBy
	 * @param string $conditionOp
	 * @return array
	 */
	public function fetchBy2($tableName, $field, $value = null, $orderBy = null, $conditionOp = 'AND') {
		if (!is_array($field)) {
			$field = [$field => $value];
		} elseif ($orderBy === null) {
			$orderBy = $value;
		}
		list($where, $values) = $this->buildSqlValuePairs($field, $conditionOp);
		$where = $where ? "WHERE $where" : '';
		$sql = "SELECT * FROM `$tableName` $where";
		if ($orderBy) {
			$sql .= " ORDER BY " . $orderBy;
		}
		return $this->buildModelsFromData($this->query($sql, $values, $tableName), $tableName);
	}

	/**
	 * Обновление данных
	 * @param array $values
	 * @param array $where
	 */
	public function update(array $values, array $where) {
		return $this->update2($this->tableName, $values, $where);
	}

	/**
	 * Обновление данных в таблице
	 * @param string $tableName
	 * @param array $values
	 * @param array $where
	 */
	public function update2($tableName, array $values, array $where) {
		if (!$values) {
			throw new \RuntimeException('$values must contain value');
		}
		list($whereSql, $whereValues) = $this->buildSqlValuePairs($where, 'AND');
		$whereSql = $whereSql ? "WHERE $whereSql" : '';
		list($updateSql, $updateValues) = $this->buildSqlValuePairs($values, 'UPDATE');
		$this->exec(
			"UPDATE :table SET $updateSql $whereSql",
			array_merge($whereValues, $updateValues),
			$tableName
		);
	}

	/**
	 * @param string $sql
	 * @param array $values
	 * @return array
	 */
	public function fetchBySql($sql, array $values) {
		return $this->fetchBySql2($this->tableName, $sql, $values);
	}

	/**
	 * @param string|null $tableName
	 * @param string $sql
	 * @param array $values
	 * @return array
	 */
	public function fetchBySql2($tableName, $sql, array $values) {
		return $this->buildModelsFromData($this->query($sql, $values, $tableName), $tableName);
	}

	/**
	 * @param string $sql
	 * @param array $values
	 * @return mixed
	 */
	public function findBySql($sql, array $values) {
		return $this->findBySql2($this->tableName, $sql, $values);
	}

	/**
	 * @param string|null $tableName
	 * @param string $sql
	 * @param array $values
	 * @return mixed
	 */
	public function findBySql2($tableName, $sql, array $values) {
		return $this->buildModelFromData($this->queryFirstRow($sql, $values, $tableName), $tableName);
	}

	/**
	 * Запуск функции в транзакции
	 * @param $callable
	 * @throws \Exception
	 */
	public function inTransaction($callable) {
		$this->startTransaction();
		try {
			call_user_func_array($callable, []);
			$this->commitTransaction();
		} catch (\Exception $e) {
			$this->rollBackTransaction();
			throw $e;
		}
	}

	/**
	 * Добавление записи. Возвращает ид новой записи
	 * @param string $tableName
	 * @param mixed[] $values
	 * @return string
	 */
	public function insert2($tableName, array $values) {
		if (count($values) == 0) {
			throw new \InvalidArgumentException("Values must not be empty");
		}
		$fields = '`' . implode('`, `', array_keys($values)) . '`';
		$valueNames = ':' . implode(', :', array_keys($values));
		$this->exec("INSERT INTO :table ($fields) VALUES ($valueNames)", $values, $tableName);
		return $this->getLastInsertId();
	}

	/**
	 * Добавление записи. Возвращает ид новой записи
	 * @param mixed[] $values
	 * @return string
	 */
	public function insert(array $values) {
		return $this->insert2($this->tableName, $values);
	}

}
