<?php

namespace bmelo\yii2\api;

use Yii;
use yii\data\BaseDataProvider;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\QueryInterface;
use yii\di\Instance;

/**
 * APIDataProvider implements a data provider based on [[\yii\db\Query]] and [[\yii\db\ActiveQuery]].
 * 
 * @author Bruno Melo <bruno.raphael@gmail.com>
 */
class DataProvider extends BaseDataProvider
{
    /**
     * @var APIConnection|array|string the DB connection object or the application component ID of the DB connection.
     * If not set, the default api_url connection will be used.
     */
    public $api;
    /**
     * @var QueryInterface the query that is used to fetch data models and [[totalCount]]
     * if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;

    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        if (is_string($this->api)) {
            $this->api = Instance::ensure($this->api, Connection::className());
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof Query) {
            throw new InvalidConfigException('The "query" property must be an instance of a class bmelo\yii2\api\Query or its subclasses.');
        }
        $query = clone $this->query;
        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }
        if (($pagination = $this->getPagination()) !== false) {
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }
        $results = $query->all($this->api);
        if( $pagination!== false ){
            $pagination->totalCount = $query->count();
        }

        return $this->buildModels( $results );
    }
    
    /**
     * Prepare models using data received from API
     * @param array $data
     * @return Model[]
     */
    protected function buildModels( $dataModels ){
        $modelClass = new $this->query->modelClass;
        $models = [];
        foreach( $dataModels as $dataModel){
            $models[] = new $modelClass($dataModel);
        }
        return $models;
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } elseif ($this->query instanceof ActiveQueryInterface) {
            /* @var $class \yii\db\ActiveRecord */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();
            if (count($pks) === 1) {
                $pk = $pks[0];
                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];
                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }
                    $keys[] = $kk;
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        $this->query->all();
        return $this->query->count();
    }

    /**
     * @inheritdoc
     */
    public function setSort($value)
    {
        parent::setSort($value);
        if (($sort = $this->getSort()) !== false && $this->query instanceof ActiveQueryInterface) {
            /* @var $model Model */
            $model = new $this->query->modelClass;
            if (empty($sort->attributes)) {
                foreach ($model->attributes() as $attribute) {
                    $sort->attributes[$attribute] = [
                        'asc' => [$attribute => SORT_ASC],
                        'desc' => [$attribute => SORT_DESC],
                        'label' => $model->getAttributeLabel($attribute),
                    ];
                }
            } else {
                foreach ($sort->attributes as $attribute => $config) {
                    if (!isset($config['label'])) {
                        $sort->attributes[$attribute]['label'] = $model->getAttributeLabel($attribute);
                    }
                }
            }
        }
    }
}
