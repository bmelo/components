<?php

namespace bmelo\yii2\api;

use Yii;

/**
 * ActiveAPIQuery represents a API query associated with an API model class.
 *
 * @author Bruno Melo <bruno.raphael@gmail.com>
 */
class ActiveQuery extends Query {

    use \yii\db\ActiveQueryTrait;
    use \yii\db\ActiveRelationTrait;

    /**
     * @event Event an event that is triggered when the query is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';
    
    /**
     * @var RequestBuilder request generator
     */
    public $builder;

    /**
     * @var string|array Request to be done
     */
    public $request;

    /**
     * @var string|array the join condition to be used when this query is used in a relational context.
     * The condition will be used in the ON part when [[ActiveQuery::joinWith()]] is called.
     * Otherwise, the condition will be used in the WHERE part of a query.
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * @see onCondition()
     */
    public $on;

    /**
     * @var array a list of relations that this query should be joined with
     */
    public $joinWith;

    /**
     * Constructor.
     * @param string $modelClass the model class associated with this query
     * @param array $config configurations to be applied to the newly created query object
     */
    public function __construct($modelClass, $config = []) {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function prepare($builder) {    
        $this->builder = $builder;
        return $this;
    }

    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($api = null) {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        if ($api === null) {
            $api = $modelClass::getApi();
        }

        if ($this->request === null) {
            list ($request, $params) = $api->getRequestBuilder()->build($this);
        } else {
            $request = $this->request;
            $params = $this->params;
        }

        $command = $api->createCommand($request, $params);
        $this->_lastCommand = $command;
        
        return $command;
    }

}
