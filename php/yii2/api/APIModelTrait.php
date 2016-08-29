<?php

namespace bmelo\yii2\api;

use Yii;

/**
 * Description of ModelAPI
 *
 * @author Bruno
 */
trait APIModelTrait {
    
    public static function getDb(){
        throw new \yii\base\Exception('This class doesn\'t use a database. Review your code.');
    }
    
    public function attributes(){
        return array_keys( $this->attributeLabels() );
    }
    
    public static function find()
    {
        return Yii::createObject(APIActiveQuery::className(), [get_called_class()]);
    }
    
}
