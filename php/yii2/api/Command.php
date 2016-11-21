<?php

namespace bmelo\yii2\api;

use Yii;
use yii\base\Component;
use yii\helpers\Json;
use maxwen\yii\curl\Curl;
use yii\base\NotSupportedException;

/**
 * Command represents a Request statement to be executed against a API RESTFul.
 *
 * @author Bruno Melo <bruno.raphael@gmail.com>
 * @since 2.0
 */
class Command extends Component
{
    /**
     * @var Connection the DB connection that this command is associated with
     */
    public $api;
    /**
     * @var array the parameters (name => value) that are bound to the current PDO statement.
     * This property is maintained by methods such as [[bindValue()]]. It is mainly provided for logging purpose
     * and is used to generate [[rawRequest]]. Do not modify it directly.
     */
    public $params = [];
    /**
     * @var integer the default number of seconds that query results can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire. And use a negative number to indicate
     * query cache should not be used.
     * @see cache()
     */
    public $queryCacheDuration;
    /**
     * @var \yii\caching\Dependency the dependency to be associated with the cached query result for this command
     * @see cache()
     */
    public $queryCacheDependency;
    /**
     * @var string with the action to be executed
     */
    private $_action;
    /**
     * @var string with the HTTP method to be executed
     */
    private $_method;
    /**
     * @var string the SQL statement that this command represents
     */
    private $_request = null;
    /**
     * @var maxwen\yii\curl\Response the response header received from the last call
     */
    private $_response;


    /**
     * Enables query cache for this command.
     * @param integer $duration the number of seconds that query result of this command can remain valid in the cache.
     * If this is not set, the value of [[Connection::queryCacheDuration]] will be used instead.
     * Use 0 to indicate that the cached data will never expire.
     * @param \yii\caching\Dependency $dependency the cache dependency associated with the cached query result.
     * @return $this the command object itself
     */
    public function cache($duration = null, $dependency = null)
    {
        $this->queryCacheDuration = $duration === null ? $this->db->queryCacheDuration : $duration;
        $this->queryCacheDependency = $dependency;
        return $this;
    }

    /**
     * Disables query cache for this command.
     * @return $this the command object itself
     */
    public function noCache()
    {
        $this->queryCacheDuration = -1;
        return $this;
    }

    /**
     * Returns the URL request statement for this command.
     * @return string the URL request statement to be executed
     */
    public function getRequest($action = null)
    {
        if( $this->_request == '' ){
            $url = trim($this->api->url, " \t/");
            $this->_request = $url.'/'.$action;
        }
        return $this->_request;
    }

    /**
     * Specifies the SQL statement to be executed.
     * The previous SQL execution (if any) will be cancelled, and [[params]] will be cleared as well.
     * @param string $request the Request statement to be set.
     * @return $this this command instance
     */
    public function setRequest($request)
    {
        if ($request !== $this->_request) {
            $this->_request = $request;
            $this->params = [];
        }

        return $this;
    }
    
    /**
     * Executes the SQL statement and returns ALL rows at once.
     * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array all rows of the query result. Each array element is an array representing a row of data.
     * An empty array is returned if the query results in nothing.
     * @throws Exception execution failed
     */
    public function headAll($fetchMode = null)
    {
        return $this->execute('head');
    }

    /**
     * Executes the SQL statement and returns query result.
     * This method is for executing a SQL query that returns result set, such as `SELECT`.
     * @return DataReader the reader object for fetching the query result
     * @throws Exception execution failed
     */
    public function query()
    {
        return $this->queryInternal('');
    }

    /**
     * Executes the SQL statement and returns ALL rows at once.
     * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array all rows of the query result. Each array element is an array representing a row of data.
     * An empty array is returned if the query results in nothing.
     * @throws Exception execution failed
     */
    public function queryAll($fetchMode = null)
    {
        return $this->queryInternal('', 'GET', $fetchMode);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     * This method is best used when only the first row of result is needed for a query.
     * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array|false the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     * @throws Exception execution failed
     */
    public function queryOne($fetchMode = null)
    {
        return $this->queryInternal('fetch', $fetchMode);
    }

    /**
     * Executes the SQL statement and returns the value of the first column in the first row of data.
     * This method is best used when only a single value is needed for a query.
     * @return string|null|false the value of the first column in the first row of the query result.
     * False is returned if there is no value.
     * @throws Exception execution failed
     */
    public function queryScalar()
    {
        $result = $this->queryInternal('fetchColumn', 0);
        if (is_resource($result) && get_resource_type($result) === 'stream') {
            return stream_get_contents($result);
        } else {
            return $result;
        }
    }

    /**
     * Creates an INSERT command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->insert('user', [
     *     'name' => 'Sam',
     *     'age' => 30,
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     * @return $this the command object itself
     */
    public function insert($table, $columns)
    {
        $params = [];
        $sql = $this->api->getRequestBuilder()->insert($table, $columns, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a batch INSERT command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and quote the values to be inserted.
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * Also note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array $rows the rows to be batch inserted into the table
     * @return $this the command object itself
     */
    public function batchInsert($table, $columns, $rows)
    {
        $sql = $this->api->getRequestBuilder()->batchInsert($table, $columns, $rows);

        return $this->setRequest($sql);
    }

    /**
     * Creates an UPDATE command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();
     * ```
     *
     * The method will properly escape the column names and bind the values to be updated.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the parameters to be bound to the command
     * @return $this the command object itself
     */
    public function update($table, $columns, $condition = '', $params = [])
    {
        $request = $this->api->getRequestBuilder()->update($table, $columns, $condition, $params);

        return $this->setRequest($request);
    }

    /**
     * Creates a DELETE command.
     * For example,
     *
     * ```php
     * $connection->createCommand()->delete('user', 'status = 0')->execute();
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table where the data will be deleted from.
     * @param string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the parameters to be bound to the command
     * @return $this the command object itself
     */
    public function delete($table, $condition = '', $params = [])
    {
        $request = $this->api->getRequestBuilder()->delete($table, $condition, $params);

        return $this->setRequest($request);
    }

    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return integer number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function execute($method = 'get')
    {
        $request = $this->getRequest();
        $key = "$method $request?" . http_build_query($this->params);
        Yii::info($key, __METHOD__);

        if ($request == '') {
            return 0;
        }

        $token = $request;
        try {
            Yii::beginProfile($token, __METHOD__);
            
            $curl = new Curl();
            $this->_response = $curl->$method($request, $this->params );

            Yii::endProfile($token, __METHOD__);
            //Total of items returned
            return $this->_response->headers['X-Pagination-Per-Page'];
        } catch (\Exception $e) {
            Yii::endProfile($token, __METHOD__);
            throw $e;
        }
    }

    /**
     * Performs the actual DB query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws Exception if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        $request = $this->getRequest();

        Yii::info($request, 'bmelo\yii2\api\Command::query');

        if ($method !== '') {
            $info = $this->api->getQueryCacheInfo($this->queryCacheDuration, $this->queryCacheDependency);
            if (is_array($info)) {
                /* @var $cache \yii\caching\Cache */
                $cache = $info[0];
                $cacheKey = [
                    __CLASS__,
                    $method,
                    $fetchMode,
                    $this->api->url,
                    $request,
                ];
                $result = $cache->get($cacheKey);
                if (is_array($result) && isset($result[0])) {
                    Yii::trace('Query result served from cache', 'bmelo\yii2\api\Command::query');
                    return $result[0];
                }
            }
        }

        $token = $request;
        try {
            Yii::beginProfile($token, 'bmelo\yii2\api\Command::query');

            $this->execute();
            $result = Json::decode( $this->_response->body );

            Yii::endProfile($token, 'bmelo\yii2\api\Command::query');
        } catch (\Exception $e) {
            Yii::endProfile($token, 'bmelo\yii2\api\Command::query');
            throw $e;
        }

        if (isset($cache, $cacheKey, $info)) {
            $cache->set($cacheKey, [$result], $info[1], $info[2]);
            Yii::trace('Saved query result in cache', 'bmelo\yii2\api\Command::query');
        }

        return $result;
    }
    
    public function getResponseHeader( $item = null ){
        if(is_string($item) ){
            return $this->_response->headers[$item];
        }
        return $this->_response->headers;
    }
}
