<?php
/**
 * Created by PhpStorm.
 * User: pravdin
 * Date: 29.04.2017
 * Time: 20:25
 */
namespace App\Db;

/**
 * MySQL DB routines
 */
class MySqlHelper {
	/**
	 * @var \PDO
	 */
	protected $connection;
	/**
	 * @var string
	 */
	protected $tableName;
	/**
	 * @var string
	 */
	protected $escapeChar;
	/**
	 * @var array
	 */
	private static $queries = [];
	/**
	 * @var bool
	 */
	private static $logQueries = false;

	/**
	 * @param \PDO $dbConnection
	 * @param string|null $tableName
	 */
	public function __construct(\PDO $dbConnection, $tableName = null) {
		$this->connection = $dbConnection;
		$this->tableName = $tableName;
		$this->escapeChar = (property_exists($dbConnection, '_driverName') && $dbConnection->_driverName == 'pgsql') ? '"' : '`';
	}

	/**
	 * @return null|string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @return string
	 */
	public function getEscapeChar() {
		return $this->escapeChar;
	}

	/**
	 * Returns all rows in the result set
	 * @param $result \PDOStatement
	 * @return array
	 */
	protected function _fetchRows($result) {
		return $result->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * Returns row from the result set
	 * @param $result \PDOStatement
	 * @return object|null
	 */
	protected function _fetchRow($result) {
		$r = $result->fetch(\PDO::FETCH_OBJ);
		return ($r !== false) ? $r : null;
	}

	/**
	 * Returns string value of the date
	 * @param \DateTime $value
	 * @return string
	 */
	protected function parseDate(\DateTime $value) {
		if ($value->getTimestamp() % 86400 == 0) {
			return $value->format("Y-m-d");
		} else {
			return $value->format("Y-m-d H:i:s");
		}
	}

	/**
	 * Assign a parameter value checking its type
	 * @param \PDOStatement $s
	 * @param $name
	 * @param $value
	 */
	protected function doAssignParam(\PDOStatement $s, $name, $value) {
		if ($value instanceof \DateTime) {
			$s->bindValue($name, $this->parseDate($value));
		} elseif (is_int($value)) {
			$s->bindValue($name, $value, \PDO::PARAM_INT);
		} elseif (is_float($value)) {
			// format float in english format only
			$f = new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
			$f->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
			$f->setAttribute(\NumberFormatter::FRACTION_DIGITS, 9);
			$s->bindValue($name, $f->format($value));
		} elseif (is_bool($value)) {
			$s->bindValue($name, $value, \PDO::PARAM_BOOL);
		} elseif (is_null($value)) {
			$s->bindValue($name, $value, \PDO::PARAM_NULL);
		} else {
			$s->bindValue($name, $value);
		}
	}

	/**
	 * Returns statement object prepared for query
	 * @param $sql
	 * @param array|null $values
	 * @param string|null $tableName
	 * @return \PDOStatement
	 */
	protected function prepare($sql, array $values = null, $tableName = null) {
		if (is_null($tableName)) {
			$tableName = $this->tableName;
		}
		if ($tableName) {
			$sql = preg_replace('/\s:table(\s|)/im', $this->escapeChar . $tableName . $this->escapeChar . ' ', $sql);
		}
		$isSelect = preg_match('/([^\w]|^)SELECT\s/i', $sql);
		// Prepare and expand array parameters
		if ($values) {
			foreach ($values as $name => $value) {
				if (preg_match("/:{$name}(?:[^\\w]|$)/m", $sql)) {
					if (is_array($value)) {
						$valuesMap = [];
						foreach ($value as $k => $v) {
							$valuesMap[] = ":{$name}_{$k}";
						}
						$sql = preg_replace("/(\\s|):$name(\\s|)/m", '$1(' . implode(',', $valuesMap) . ')$2', $sql);
					} elseif (is_null($value) && $isSelect) {
						// если null и запрос select, то надо равенство в запросе заменить на is null
						$pattern = "/=(\\s*|)(:{$name})([^\\w]|$)/m";
						if (preg_match($pattern, $sql)) {
							$sql = preg_replace("/=(\\s*|)(:{$name})([^\\w]|$)/m", ' IS NULL $3', $sql);
							// и удалить параметр, поскольку он больше не нужен
							unset($values[$name]);
						}
					}
				}
			}
		}
		$s = $this->connection->prepare($sql);
		if ($values) {
			foreach ($values as $name => $value) {
				if (preg_match("/:{$name}/m", $sql)) {
					if (is_array($value)) {
						foreach ($value as $k => $v) {
							$this->doAssignParam($s, "{$name}_{$k}", $v);
						}
					} else {
						$this->doAssignParam($s, $name, $value);
					}
				}
			}
		}
		return $s;
	}

	/**
	 * @param $stmt \PDOStatement
	 * @throws \Exception
	 * @throws \PDOException
	 */
	protected function doExecute($stmt) {
		if (self::$logQueries) {
			$t = microtime(true);
		}
		if (!$stmt->execute()) {
			throw new \Exception('Database error ' . $this->connection->errorCode() . ': ' . $this->connection->errorInfo());
		}
		if (self::$logQueries) {
			$t2 = microtime(true);
			self::$queries[] = [$stmt->queryString, $t2 - $t];
		}
	}

	/**
	 * Execute query and return its result as array of plain objects
	 * @param $sql
	 * @param array|null $values Параметры запроса
	 * @param string|null $tableName
	 * @return array
	 * @throws \Exception
	 */
	public function query($sql, array $values = null, $tableName = null) {
		$s = $this->prepare($sql, $values, $tableName);
		$this->doExecute($s);
		$result = $this->_fetchRows($s);
		$s->closeCursor();
		return $result;
	}

	/**
	 * Execute query and return Statement object for manual rows fetching
	 * @param $sql
	 * @param array|null $values Параметры запроса
	 * @param string|null $tableName
	 * @return \PDOStatement
	 */
	public function queryStmt($sql, array $values = null, $tableName = null) {
		$s = $this->prepare($sql, $values, $tableName);
		$this->doExecute($s);
		return $s;
	}

	/**
	 * Get a row from the result
	 * @param \PDOStatement $statement
	 * @return mixed
	 */
	public function stmtFetchRow(\PDOStatement $statement) {
		return $statement->fetchObject();
	}

	/**
	 * Free result
	 * @param \PDOStatement $statement
	 */
	public function stmtClose(\PDOStatement $statement) {
		$statement->closeCursor();
	}

	/**
	 * Execute query and return first row
	 * @param $sql
	 * @param array|null $values
	 * @param string|null $tableName
	 * @return object|null
	 * @throws \Exception
	 */
	public function queryFirstRow($sql, array $values = null, $tableName = null) {
		$s = $this->prepare($sql, $values, $tableName);
		$this->doExecute($s);
		$result = $this->_fetchRow($s);
		$s->closeCursor();
		return $result;
	}

	/**
	 * Execute query and return first value from first row
	 * @param $sql
	 * @param array|null $values
	 * @param string|null $tableName
	 * @return mixed
	 * @throws \Exception
	 */
	public function queryFirstValue($sql, array $values = null, $tableName = null) {
		$s = $this->prepare($sql, $values, $tableName);
		$this->doExecute($s);
		$r = $this->_fetchRow($s);
		$s->closeCursor();
		$r = (array)$r;
		return $r ? reset($r) : null;
	}

	/**
	 * Execute query and return modified rows count
	 * @param $sql
	 * @param array|null $values
	 * @param string|null $tableName
	 * @return int
	 * @throws \Exception
	 */
	public function exec($sql, array $values = null, $tableName = null) {
		$s = $this->prepare($sql, $values, $tableName);
		$this->doExecute($s);
		$result = $s->rowCount();
		$s->closeCursor();
		return $result;
	}

	/**
	 * Start transaction
	 */
	public function startTransaction() {
		$this->connection->beginTransaction();
	}

	/**
	 * Commit transaction
	 */
	public function commitTransaction() {
		$this->connection->commit();
	}

	/**
	 * Rollback transaction
	 */
	public function rollBackTransaction() {
		$this->connection->rollBack();
	}

	/**
	 * Last insert id
	 * @return string
	 */
	public function getLastInsertId() {
		return $this->connection->lastInsertId();
	}

	/**
	 * Make value DB-safe
	 * @param string $v
	 * @param int $type
	 * @return string
	 */
	public function escape($v, $type = \PDO::PARAM_STR) {
		return $this->connection->quote($v, $type);
	}

	/**
	 * Format timestamp to use with DB
	 * @param int $timestamp
	 * @return string
	 */
	public function formatDateTime($timestamp) {
		return date('Y-m-d H:i:s', $timestamp);
	}

	/**
	 * Enable or disable query log
	 * @param bool $value
	 */
	public static function setLogQueries($value) {
		self::$logQueries = $value;
	}

	/**
	 * Get query log
	 * @return string[]
	 */
	public static function getQueries() {
		return self::$queries;
	}
}
