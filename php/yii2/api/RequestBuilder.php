<?php

namespace bmelo\yii2\api;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * ActiveAPIQuery represents a API query associated with an API model class.
 *
 * @author Bruno Melo <bruno.raphael@gmail.com>
 */
class RequestBuilder extends \yii\base\Object {

    public $api;
    public $separator = ',';
    private $_baseUrl = null;

    public function implode($data) {
        return implode($this->separator, (array) $data);
    }

    /**
     * Generates a SELECT SQL statement from a [[Query]] object.
     * @param Query $query the [[Query]] object from which the SQL statement will be generated.
     * @param array $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array the generated SQL statement (the first array element) and the corresponding
     * parameters to be bound to the SQL statement (the second array element). The parameters returned
     * include those provided in `$params`.
     */
    public function build($query, $params = []) {
        $modelClass = $query->modelClass;
        $this->_baseUrl = $modelClass::getApiUrlBase();

        $query->prepare($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = ArrayHelper::merge(
            $this->buildSelect($query->select, $query->distinct, $query->selectOption), 
            $this->buildJoin($query->join, $params), 
            $this->buildOrderByAndLimit($query->orderBy, $query->limit, $query->offset)
            //$this->buildWhere($query->where, $params)
        );

        if (!empty($query->orderBy)) {
            foreach ($query->orderBy as $expression) {
                if ($expression instanceof Expression) {
                    $params = array_merge($params, $expression->params);
                }
            }
        }

        return [$this->_baseUrl, $clauses];
    }

    /**
     * @param array $columns
     * @param boolean $distinct
     * @param string $selectKey
     * @return string the SELECT clause built from [[Query::$select]].
     */
    public function buildSelect($columns, $distinct = false, $selectKey = 'fields') {
        $query = [];
        if ($distinct) {
            $query['distinct'] = $distinct;
        }
        if (!empty($columns)) {
            $query[$selectKey] = $this->implode($columns);
        }
        return $query;
    }

    /**
     * @param array $joins
     * @param array $params the binding parameters to be populated
     * @return string the JOIN clause built from [[Query::$join]].
     * @throws Exception if the $joins parameter is not in proper format
     */
    public function buildJoin($joins) {
        $query = [];
        if (!empty($joins)) {
            $query = $this->implode(array_keys($joins));
        }

        return $query;
    }

    /**
     * @param string|array $condition
     * @param array $params the binding parameters to be populated
     * @return string the WHERE clause built from [[Query::$where]].
     */
    public function buildWhere($condition, &$params) {
        $where = $this->buildCondition($condition, $params);

        return $where === '' ? '' : 'WHERE ' . $where;
    }

    /**
     * Parses the condition specification and generates the corresponding SQL expression.
     * @param string|array|Expression $condition the condition specification. Please refer to [[Query::where()]]
     * on how to specify a condition.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     */
    public function buildCondition($condition, &$params) {
        if ($condition instanceof Expression) {
            foreach ($condition->params as $n => $v) {
                $params[$n] = $v;
            }
            return $condition->expression;
        } elseif (!is_array($condition)) {
            return (string) $condition;
        } elseif (empty($condition)) {
            return '';
        }

        if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
            $operator = strtoupper($condition[0]);
            if (isset($this->conditionBuilders[$operator])) {
                $method = $this->conditionBuilders[$operator];
            } else {
                $method = 'buildSimpleCondition';
            }
            array_shift($condition);
            return $this->$method($operator, $condition, $params);
        } else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
            return $this->buildHashCondition($condition, $params);
        }
    }

    /**
     * Creates an SQL expressions like `"column" operator value`.
     * @param string $operator the operator to use. Anything could be used e.g. `>`, `<=`, etc.
     * @param array $operands contains two column names.
     * @param array $params the binding parameters to be populated
     * @return string the generated SQL expression
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    public function buildSimpleCondition($operator, $operands, &$params) {
        if (count($operands) !== 2) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }

        list($column, $value) = $operands;

        if (strpos($column, '(') === false) {
            $column = $this->db->quoteColumnName($column);
        }

        if ($value === null) {
            return "$column $operator NULL";
        } elseif ($value instanceof Expression) {
            foreach ($value->params as $n => $v) {
                $params[$n] = $v;
            }
            return "$column $operator {$value->expression}";
        } elseif ($value instanceof Query) {
            list($sql, $params) = $this->build($value, $params);
            return "$column $operator ($sql)";
        } else {
            $phName = self::PARAM_PREFIX . count($params);
            $params[$phName] = $value;
            return "$column $operator $phName";
        }
    }

    /**
     * Builds the ORDER BY and LIMIT/OFFSET clauses and appends them to the given SQL.
     * @param array $orderBy the order by columns. See [[Query::orderBy]] for more details on how to specify this parameter.
     * @param integer $limit the limit number. See [[Query::limit]] for more details.
     * @param integer $offset the offset number. See [[Query::offset]] for more details.
     * @return string the SQL completed with ORDER BY/LIMIT/OFFSET (if any)
     */
    public function buildOrderByAndLimit($orderBy, $limit, $offset) {
        return ArrayHelper::merge(
                        $this->buildOrderBy($orderBy), $this->buildLimit($limit, $offset)
        );
    }

    /**
     * @param array $columns
     * @return string the ORDER BY clause built from [[Query::$orderBy]].
     */
    public function buildOrderBy($columns) {
        $query = $orders = [];
        foreach ($columns as $name => $direction) {
            if ($direction === SORT_DESC) {
                $name = '-' . $name;
            }
            $orders[] = $name;
        }

        if (!empty($orders)) {
            $query['sort'] = $this->implode($orders);
        }

        return $query;
    }

    /**
     * @param integer $limit
     * @param integer $offset
     * @return string the LIMIT and OFFSET clauses
     */
    public function buildLimit($limit, $offset) {
        $query = [];
        if ($this->hasLimit($limit)) {
            $query['per-page'] = $limit;
        }
        if ($this->hasOffset($offset)) {
            $query['page'] = $offset;
        }

        return $query;
    }

    /**
     * Checks to see if the given limit is effective.
     * @param mixed $limit the given limit
     * @return boolean whether the limit is effective
     */
    protected function hasLimit($limit) {
        return ctype_digit((string) $limit);
    }

    /**
     * Checks to see if the given offset is effective.
     * @param mixed $offset the given offset
     * @return boolean whether the offset is effective
     */
    protected function hasOffset($offset) {
        $offset = (string) $offset;
        return ctype_digit($offset) && $offset !== '0';
    }

}
