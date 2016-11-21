<?php

namespace bmelo\yii2\api;

use Yii;

/**
 * Description of ModelAPI
 *
 * @author Bruno
 */
trait ModelTrait {
    
    public static function getDb(){
        throw new \yii\base\Exception('This class doesn\'t use a database. Review your code.');
    }
    
    /**
     * Returns the database connection used by this AR class.
     * By default, the "api" application component is used as the database connection.
     * You may override this method if you want to use a different database connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getApi()
    {
        return Yii::$app->api;
    }
    
    public static function getApiEntity()
    {
        $class = get_class();
        $className = substr($class, strrpos($class, '\\')+1);
        return strtolower($className);
    }
    
    public static function getApiUrlBase(){
        return trim(static::getApi()->url, '/') . '/' . static::getApiEntity();
    }
    
    public function attributes(){
        return array_keys( $this->attributeLabels() );
    }
    
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [ get_called_class()]);
    }
    
    public function __set( $name, $value ){
        try{
            parent::__set($name, $value);
        }catch(\yii\base\InvalidCallException $e){
            if( !$this->createRelation( $name, $value ) ){
                throw $e;
            }
        }
    }
    
    public function createRelation($name, $value){
        // Checking if is a relation
        $relation = call_user_func([$this, 'get' . ucfirst($name)]);
        if( $relation instanceof yii\db\ActiveQuery ) {
            if( !$relation->multiple ){
                $models = new $relation->modelClass ($value);
            } else {
                foreach( $value as $item ){
                    $models = new $relation->modelClass ($item);
                }
            }
            $this->populateRelation($name, $models);
            return true;
        }
        return false;
    }
    
}
