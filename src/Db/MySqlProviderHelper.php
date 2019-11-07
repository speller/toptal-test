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
class MySqlProviderHelper extends MySqlHelper
{
    /**
     * Build model object from raw data
     * @param object|null $data
     * @param string|null $tableName
     * @return null|object
     */
    public function buildModelFromData($data, $tableName)
    {
        if (!is_null($data)) {
            $method = 'build' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
            if (method_exists($this, $method)) {
                return $this->$method($data);
            } else {
                return $data;
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
    public function buildModelsFromData(array $data, $tableName): array
    {
        return array_map([$this, 'buildModelFromData'], $data, array_pad([], count($data), $tableName));
    }

    /**
     * Delete row by id
     * @param mixed $id
     * @throws \Exception
     */
    public function deleteById($id): void
    {
        $this->deleteById2($this->tableName, $id);
    }

    /**
     * Delete row by id
     * @param string $tableName
     * @param mixed $id
     * @throws \Exception
     */
    public function deleteById2(string $tableName, $id): void
    {
        $this->exec(
            sprintf('DELETE FROM :table WHERE id %s :id', is_array($id) ? 'IN' : '='),
            ['id' => $id],
            $tableName
        );
    }

    /**
     * Delete by specific conditions
     * @param string|array $field
     * @param mixed $value
     * @param string $conditionOp
     * @return mixed|null
     * @throws \Exception
     */
    public function deleteBy($field, $value = null, $conditionOp = 'AND')
    {
        return $this->deleteBy2($this->tableName, $field, $value, $conditionOp);
    }

    /**
     * Delete by specific conditions in a table
     * @param string $tableName
     * @param string|array $field
     * @param mixed $value
     * @param string $conditionOp
     * @return mixed|null
     * @throws \Exception
     */
    public function deleteBy2(string $tableName, $field, $value = null, $conditionOp = 'AND')
    {
        if (!is_array($field)) {
            $field = [$field => $value];
        }
        list($where, $values) = $this->buildSqlValuePairs($field, $conditionOp);
        $where = $where ? "WHERE $where" : '';
        $sql = "DELETE FROM :table $where";
        return $this->exec($sql, $values, $tableName);
    }

    /**
     * Find a row by id
     * @param mixed $id
     * @return mixed
     * @throws \Exception
     */
    public function findById($id)
    {
        return $this->findById2($this->tableName, $id);
    }

    /**
     * Find a row by id in custom table
     * @param string $tableName
     * @param mixed $id
     * @return mixed
     * @throws \Exception
     */
    public function findById2(string $tableName, $id)
    {
        return
            $this->buildModelFromData(
                $this->queryFirstRow(
                    'SELECT * FROM :table WHERE id = :id', ['id' => $id], $tableName
                ),
                $tableName
            );
    }

    /**
     * Find a row by specific conditions
     * @param string|array $field
     * @param mixed $value
     * @param string|null $orderBy
     * @param string $conditionOp
     * @return mixed|null
     * @throws \Exception
     */
    public function findBy($field, $value = null, $orderBy = null, $conditionOp = 'AND')
    {
        return $this->findBy2($this->tableName, $field, $value, $orderBy, $conditionOp);
    }

    /**
     * Find a row in custom table by specific conditions
     * @param string|null $tableName
     * @param string|array $field
     * @param mixed $value
     * @param null $orderBy
     * @param string $conditionOp
     * @return mixed|null
     * @throws \Exception
     */
    public function findBy2(string $tableName, $field, $value = null, $orderBy = null, $conditionOp = 'AND')
    {
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
     * Fetch rows by specific criteria
     * @param string|array $field
     * @param mixed $value field value or sort order if $field is array
     * @param string|null $orderBy
     * @param string $conditionOp
     * @return array
     * @throws \Exception
     */
    public function fetchBy($field, $value = null, $orderBy = null, $conditionOp = 'AND')
    {
        return $this->fetchBy2($this->tableName, $field, $value, $orderBy, $conditionOp);
    }

    /**
     * Build SQL pairs `field` = `value` taking care of arrays and nulls.
     * For update SQL use $operation = 'UPDATE', to avoid setting 'IS NULL'
     * @param array $fieldValues
     * @param string $operation
     * @return array [SQL, values]
     */
    protected function buildSqlValuePairs(array $fieldValues, string $operation): array
    {
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
     * Fetch array of rows in a custom table by conditions
     * @param string|null $tableName
     * @param string|array $field
     * @param mixed $value field value or sort order if $field is array
     * @param string|null $orderBy
     * @param string $conditionOp
     * @return array
     * @throws \Exception
     */
    public function fetchBy2(string $tableName, $field, $value = null, $orderBy = null, $conditionOp = 'AND'): array
    {
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
     * Update data
     * @param array $values
     * @param array $where
     * @throws \Exception
     */
    public function update(array $values, array $where): void
    {
        $this->update2($this->tableName, $values, $where);
    }

    /**
     * Update data in a custom table
     * @param string $tableName
     * @param array $values
     * @param array $where
     * @throws \Exception
     */
    public function update2($tableName, array $values, array $where): void
    {
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
     * Fetch array of rows by plain SQL
     * @param string $sql
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function fetchBySql(string $sql, array $values)
    {
        return $this->fetchBySql2($this->tableName, $sql, $values);
    }

    /**
     * Fetch array of rows from a custom table by plain SQL
     * @param string|null $tableName
     * @param string $sql
     * @param array $values
     * @return array
     * @throws \Exception
     */
    public function fetchBySql2(string $tableName, $sql, array $values)
    {
        return $this->buildModelsFromData($this->query($sql, $values, $tableName), $tableName);
    }

    /**
     * Find a record by plain SQL
     * @param string $sql
     * @param array $values
     * @return mixed
     * @throws \Exception
     */
    public function findBySql(string $sql, array $values)
    {
        return $this->findBySql2($this->tableName, $sql, $values);
    }

    /**
     * Find a record in a custom table by plain SQL
     * @param string|null $tableName
     * @param string $sql
     * @param array $values
     * @return mixed
     * @throws \Exception
     */
    public function findBySql2($tableName, $sql, array $values)
    {
        return $this->buildModelFromData($this->queryFirstRow($sql, $values, $tableName), $tableName);
    }

    /**
     * Run a function in transaction
     * @param $callable
     * @throws \Exception
     */
    public function inTransaction($callable)
    {
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
     * Add record in a custom table. Returns new id
     * @param string $tableName
     * @param mixed[] $values
     * @return string
     * @throws \Exception
     */
    public function insert2(string $tableName, array $values): string
    {
        if (count($values) == 0) {
            throw new \InvalidArgumentException("Values must not be empty");
        }
        $fields = '`' . implode('`, `', array_keys($values)) . '`';
        $valueNames = ':' . implode(', :', array_keys($values));
        $this->exec("INSERT INTO :table ($fields) VALUES ($valueNames)", $values, $tableName);
        return $this->getLastInsertId();
    }

    /**
     * Add record. Returns new id
     * @param mixed[] $values
     * @return string
     * @throws \Exception
     */
    public function insert(array $values): string
    {
        return $this->insert2($this->tableName, $values);
    }

}
