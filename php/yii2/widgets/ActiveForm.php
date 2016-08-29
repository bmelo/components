<?php

namespace bmelo\yii2\widgets;

/**
 * Description of ActiveForm
 *
 * @author Bruno
 */
class ActiveForm extends \yii\widgets\ActiveForm {

    /**
     * @var string the default field class name when calling [[field()]] to create a new field.
     * @see fieldConfig
     */
    public $fieldClass = '\bmelo\yii2\widgets\ActiveField';

    /**
     * Generates a form field.
     * A form field is associated with a model and an attribute. It contains a label, an input and an error message
     * and use them to interact with end users to collect their inputs for the attribute.
     * @param Model $model the data model
     * @param string $attribute the attribute name or expression. See [[Html::getAttributeName()]] for the format
     * about attribute expression.
     * @param array $options the additional configurations for the field object. These are properties of [[ActiveField]]
     * or a subclass, depending on the value of [[fieldClass]].
     * @return ActiveField the created ActiveField object
     * @see fieldConfig
     */
    public function field($model, $attribute, $options = []) {
        return parent::field($model, $attribute, $options);
    }

}
