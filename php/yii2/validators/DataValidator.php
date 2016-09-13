<?php
namespace bmelo\yii2\validators;

use yii\validators\DateValidator;
use bmelo\components\Date;
/**
 * Description of CPF
 *
 * @author Bruno
 */
class DataValidator extends DateValidator{
    
    /**
     * @inheritdoc
     */
    public $format = 'php:d/m/Y';
    public $hours = false;
    public $seconds = false;
    
    protected function treatFormat(){
        if($this->seconds){
            $this->format.=' H:i:s';
        }
        elseif($this->hours){
            $this->format.=' H:i';
        }
    }
    
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        $this->treatFormat();
        $value = $model->$attribute;
        $result = $this->validateValue($value);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }else{
            $format = str_replace('php:', '', $this->format);
            $model->$attribute = Date::formatDate($value, $format);
        }
    }
    
}
