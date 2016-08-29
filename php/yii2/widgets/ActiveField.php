<?php

namespace bmelo\yii2\widgets;

use Yii;
use yii\helpers\Html;
use yii\widgets\MaskedInput;
use yii\jui\AutoComplete;
use yii\web\View;
use yii\web\JsExpression;
use dosamigos\multiselect\MultiSelect;

/**
 * Description of ActiveForm
 *
 * @author Bruno
 */
class ActiveField extends \yii\widgets\ActiveField {

    protected function getInputId() {
        return (isset($this->inputOptions['id']) && $this->inputOptions['id']) ? $this->inputOptions['id'] : Html::getInputId($this->model, $this->attribute);
    }

    /**
     * @var string the default field class name when calling [[field()]] to create a new field.
     * @see fieldConfig
     */
    public function date($options = []) {
        $value = $this->model->{$this->attribute};
        $this->model->{$this->attribute} = \Yii::$app->formatter->asDate($value);
        return $this->mask('99/99/9999', $options);
    }

    public function dateTime($options = []) {
        $value = $this->model->{$this->attribute};
        $this->model->{$this->attribute} = \Yii::$app->formatter->asDatetime($value);
        return $this->mask('99/99/9999 99:99', $options);
    }

    public function telefone($options = []) {
        return $this->mask('(99) 9999-9999', $options);
    }

    public function celular($options = []) {
        return $this->mask('(99) 9999-9999[9]', $options);
    }

    public function cpf($options = []) {
        return $this->mask('999.999.999-99', $options);
    }

    public function mask($mask, $options = []) {
        $options = array_merge($this->inputOptions, $options);
        $this->adjustLabelFor($options);
        return $this->widget( MaskedInput::className(), ['mask' => $mask] );
    }
    
    public function multiselect( $items, $options = [] ){
        //Defaults
        $opts = array_merge( [
            "options" => ['multiple'=>"multiple"], // for the actual multiselect
            "data" => $items,
            "clientOptions" => 
                [
                    "includeSelectAllOption" => true,
                    'numberDisplayed' => 2
                ], 
        ], $options );
        return $this->widget( MultiSelect::className(), $opts);
    }

    public function autocomplete($opts) {
        $id = $this->getInputId();
        $options = array_merge([ //options for autocomplete
            'name' => $id.'_autocomplete',
            'options' => [ 
                'data-target' => $id,
                'placeholder' => ''
            ],
            'scrollable' => true,
            'pluginOptions' => ['highlight' => true],
            'dataset' => $opts['dataset']
        ], $opts);
        $hidden = Html::activeHiddenInput($this->model, $this->attribute); //hidden field
        $autocomplete = TypeaheadSelect::widget($options); //autocomplete
        Yii::$app->getView()->registerJs("$('body').on('typeahead:select', function(ev, suggestion) {
            target = '#'+$(ev.target).data('target');
            \$(target).val( suggestion.id );
          });", View::POS_END, 'autocompletecreate');
        $this->parts['{input}'] = $hidden . $autocomplete;
        return $this;
    }

    public function autocompleteJuiFree( $options ) {
        Yii::$app->getView()->registerJs("$('body').on('autocompletecreate', 'input', function(e, ui){"
                . "$(this).addClass('form-control') });", View::POS_END, 'autocompletecreate');
        return $this->widget(AutoComplete::className(), $options);
    }

    public function autocompleteJui($opts) {
        $id = $this->getInputId();
        $options = array_merge_recursive([
            'id' => $id . '_autoComplete',
            'clientOptions' => [
                'select' => new JsExpression("function( event, ui ) { $( '#{$id}' ).val( ui.item.id ); }"),
                'change' => new JsExpression("function(){ if( $( '#{$id}' ).val() <= 0 ){ $(this).val(null); }}"),
                'search' => new JsExpression("function(){ $( '#{$id}' ).val(null); }")
            ]
                ], $opts);

        $hidden = Html::activeHiddenInput($this->model, $this->attribute);
        $autocomplete = AutoComplete::widget($options);
        Yii::$app->getView()->registerJs("$('body').on('autocompletecreate', 'input', function(e, ui){"
                . "$(this).addClass('form-control') });", View::POS_END, 'autocompletecreate');

        $this->parts['{input}'] = $hidden . $autocomplete;
        return $this;
    }

}
