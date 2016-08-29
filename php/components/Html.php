<?php

/**
 * Description of MyHtml
 *
 * @author Bruno Melo <bruno.melo@idor.org>
 */

namespace bmelo\components;

class Html extends CHtml {
    
    protected static function widget($class, $params){
        return Yii::app()->controller->widget($class, $params, true);
    }
    
    public static function button($label='button', $options=[], $htmlOptions=[]) {
        $options = array_merge(compact('label','htmlOptions'), $options);
        if( isset($options['url']) ){ //Irá gerar um link com um botão
            unset( $options['htmlOptions'] );
            $btn = self::widget('booster.widgets.TbButton', $options);
            return self::link( $btn, $options['url'], $htmlOptions );
        }
        return self::widget('booster.widgets.TbButton', $options); //Gera apenas o botão
    }
    
    public static function __callStatic($name, $parameters) {        
        if ( preg_match('/^active/', $name) ) {
            $name = preg_filter('/^active/', '', $name);
            $name[0] = strtolower($name[0]); //Primeira letra em minúsculo
            if (method_exists(get_class(), $name)) {
                return call_user_func_array(array(get_class(), '_active'), array($name, $parameters));
            }
        }
        if( method_exists(get_class(), $name) )
            return call_user_func_array([get_class(), $name], $parameters);
        throw new CException( 
            Yii::t('yii','Property "{class}.{property}" is not defined.',['{class}'=>get_class(), '{property}'=>$name]) 
        );
    }
    
    protected static function _getHtmloptionsPos( $func ){
        $args = Helper::func_argNames([get_class(), $func]);
        $pos = array_search('htmlOptions', $args);
        if( $pos ){
            return $pos;
        }
        return false;
    }

    /**
     * Atalho para gerar campo já com título e área de erros.
     * $params[0] = $model
     * $params[1] = $attribute
     */
    public static function _active($func, $params) {
        $posHOpts = self::_getHtmloptionsPos($func);
        if( !$posHOpts ){ $posHOpts = 2; }
        if( !isset($params[$posHOpts]) ){ $params[$posHOpts] = []; }
        
        $dados = self::getDataToField($params[0], $params[1], $params[ $posHOpts ]);
        $params[0] = $dados['name'];
        $params[1] = $dados['value'];
        $params[ $posHOpts ] = $dados['htmlOptions'];
        return call_user_func_array( [get_class(), $func], $params);
    }

    public static function getDataToField($model, $attribute, $htmlOptions) {
        MyHtml::resolveNameID($model, $attribute, $htmlOptions);
        $name = $htmlOptions['name'];
        $value = MyHtml::resolveValue($model, $attribute);
        $label = $model->getAttributeLabel($attribute);
        return compact('name', 'value', 'label', 'htmlOptions');
    }

    public static function datepickerField($name, $value, $htmlOptions = array(), $pickerOptions = array()) {
        $id = isset($htmlOptions['id']) ? $htmlOptions['id'] : preg_filter('/[\[\]]/', '', $name);
        $pkOptions = array_merge(array(
          'name' => $name,
          'value' => $value,
          'language' => 'pt-BR',
          'options' => array(
            'showAnim' => 'fold',
            'dateFormat' => 'dd/mm/yy',
            'changeMonth' => true,
            'changeYear' => true,
            'yearRange' => (date('Y') - 5) . ':' . date('Y'),
            'constrainInput' => false,
          )), $pickerOptions);
        $pkOptions['htmlOptions'] = array_merge(array('class' => 'date'), $htmlOptions);

        $maskFormat = str_replace(array('-', 'yy'), array('/', 'yyyy'), $pkOptions['options']['dateFormat']);
        $js = "jQuery(\"#{$id}\").mask(\"" . preg_replace('/[dmy]/', '9', $maskFormat) . "\");";
        Yii::app()->clientScript->registerCoreScript('maskedinput');
        Yii::app()->clientScript->registerScript("datepicker#{$id}", $js);

        return self::widget('zii.widgets.jui.CJuiDatePicker', $pkOptions);
    }

    public static function activeDateField($model, $attribute, $htmlOptions = array()) {
        return self::_active('dateField', array($model, $attribute, $htmlOptions));
    }

    public static function dateField($name, $value = '', $htmlOptions = array()) {
        $value = DateTools::dataFormat($value, 'd/m/Y H:i:s');
        $htmlOptions = array_merge(array('class' => 'date'), $htmlOptions);
        $out = parent::textField($name, $value, $htmlOptions);
        $js = "jQuery('input.date').mask('99/99/9999');";
        Yii::app()->clientScript->registerCoreScript('maskedinput');
        Yii::app()->clientScript->registerScript('datefilter', $js);
        return $out;
    }

    public static function timeField($name, $value = '', $htmlOptions = array()) {
        $htmlOptions = array_merge(array('class' => 'time'), $htmlOptions);
        $out = parent::textField($name, $value, $htmlOptions);
        $id = $htmlOptions['id'];
        $js = "jQuery('#{$id}').mask('99:99');";
        Yii::app()->clientScript->registerCoreScript('maskedinput');
        Yii::app()->clientScript->registerScript('timefilter#' . $id, $js);
        return $out;
    }

    public static function dateTimeField($name, $value = '', $htmlOptions = array()) {
        $id = $htmlOptions['id'];
        $value = DateTools::dataFormat($value, 'd/m/Y H:i:s');
        $dataValue = $timeValue = '';
        if (!empty($value)) {
            $dataValue = substr($value, 0, 10);
            $timeValue = substr($value, 11);
        }

        //Javascript necessário
        $jsFuncChange = "function changeValues( data, time, dataTime ){ "
            . "var valor = jQuery(data).val()+' '+jQuery(time).val(); "
            . "jQuery(dataTime).val( valor ); }";
        Yii::app()->clientScript->registerScript('funcDateTimeField', $jsFuncChange, CClientScript::POS_HEAD);
        $idData = $id . '_dataField';
        $idTime = $id . '_timeField';
        $jsChange = "changeValues('#{$idData}','#{$idTime}','#{$id}');";

        $out = MyHtml::datepickerField('', $dataValue, array_merge($htmlOptions, array('id' => $idData, 'name' => '', 'onchange' => $jsChange)));
        $out.= MyHtml::timeField('', $timeValue, array_merge($htmlOptions, array('id' => $idTime, 'name' => '', 'onchange' => $jsChange)));
        $out.= MyHtml::hiddenField($name, $value);
        return self::tag('div', array('class' => 'dateTimeField'), $out);
    }

    public static function dateRange($model, $attr, $value = array(), $htmlOptions = array()) {
        $val0 = isset($value[0]) ? $value[0] : null;
        $val1 = isset($value[1]) ? $value[1] : null;
        return MyHtml::dateField($model, $attr . '[0]', $val0) .
            '<br/>Até<br/>' .
            MyHtml::dateField($model, $attr . '[1]', $val1);
    }
    
    public static function dateRange2($name, $value, $htmlOptions = []) {
        return self::widget('custom.extensions.form.dateRange.DateRange', compact('value','htmlOptions'));
    }

    public static function textEditor($name, $value, $ckeOptions = array()) {
        $obrigatorios = array(
          'name' => $name,
          'value' => $value,
          'toolbar' => array(
            ['Bold', 'Italic', 'Underline', '-', 'BulletedList', '-', 'Link', 'Unlink'],
            ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
            ['FontSize', 'TextColor', 'Maximize']
          ),
          'ckeConfig' => array('removePlugins' => 'elementspath', 'resize_enabled' => false, 'title' => '',),
        );
        $options = array_merge($obrigatorios, $ckeOptions);
        return self::widget('custom.extensions.editMe.widgets.ExtEditMe', $options);
    }

    //Só aceita chave de registros que já existem no banco
    public static function textOption($name, $value, $options = array()) {
        $id = $options['id'];
        $juiOptions = [
          'name' => $name . '_autoComplete',
          'value' => null,
          'sourceUrl' => Yii::app()->controller->route,
          'options' => [
            'minLength' => 3,
            'select' => "js:function( event, ui ) { $( '#{$id}' ).val( ui.item.id ); }",
            'change' => "js:function(){ if( $( '#{$id}' ).val() <= 0 ){ $(this).val(null); }}",
            'search' => "js:function(){ $( '#{$id}' ).val(null); }"
          ]
        ];
        if (isset($options['juiOptions']))
            $juiOptions = array_merge($juiOptions, $options['juiOptions']);
        $out = Yii::app()->controller->widget('zii.widgets.jui.CJuiAutoComplete', $juiOptions, true);
        if (!isset($options['hidden']) or $options['hidden'])
            $out .= self::hiddenField($name, $value);
        return $out;
    }

    //Autocomplete que apenas sugere valores, o resultado pode ser qualquer valor
    public static function textOptionFree($name, $value, $options = array()) {
        $options['hidden'] = false;
        if (!isset($options['juiOptions']))
            $options['juiOptions'] = array();
        $options['juiOptions']['name'] = $name;
        $options['juiOptions']['options'] = array();
        return self::textOption($name, $value, $options);
    }

    public static function activeMultipleChoice($model, $attr, $options, $htmlOptions = array()) {
        $dados = self::getDataToField($model, $attr, $htmlOptions);
        return self::multipleChoice($dados['name'], $dados['value'], $options, $dados['htmlOptions']);
    }

    public static function multipleChoice($name, $values, $options, $htmlOptions = array()) {
        return self::widget('custom.extensions.form.multipleChoice.MChoices', compact('name', 'values', 'options', 'htmlOptions'));
    }
    
    public static function activeMultiselectDropDown($model, $attr, $options, $htmlOptions = array()) {
        $dados = self::getDataToField($model, $attr, $htmlOptions);
        return self::multiselectDropDown($dados['name'], $dados['value'], $options, $dados['htmlOptions']);
    }

    public static function multiselectDropDown($name, $value, $options, $htmlOptions = array()) {
        return self::widget('custom.extensions.form.multiselect.multiDropDown', compact('name','value','options','htmlOptions'));
    }
    
    public static function activeMultiselect($model, $attr, $options, $htmlOptions = array()) {
        $dados = self::getDataToField($model, $attr, $htmlOptions);
        return self::multiselectDropDown($dados['name'], $dados['value'], $options, $dados['htmlOptions']);
    }

    public static function multiselect($name, $value, $options, $htmlOptions = array()) {
        return self::widget('custom.extensions.form.multiselect.multiDropDown', compact('name','value','options','htmlOptions'));
    }

    public static function optionCk($name, $label, $checked = false, $inline = true, $htmlOptions = array()) {
        $for = CVar::getItemArr($htmlOptions, 'id', $name);
        $htmlOptions['class'] = CVar::getItemArr($htmlOptions, 'class', '').' optCk';
        $ck = static::checkBox('', $checked, array_merge($htmlOptions, ['data-for'=>$for]));
        
        $label = static::label($label, $for, array('class' => $inline ? 'inline' : ''));
        $ck .= static::hiddenField($name, $checked);
        $js =<<<EOJ
            $(document).on('change', '.optCk[type=checkbox]', function(){ 
                var checked = $(this).is(':checked') ? 1 : 0;
                $("input[name='"+$(this).data('for')+"']").val( checked );
            } );
EOJ;
        Yii::app()->clientScript->registerScript('checkUpdate', $js, CClientScript::POS_END);
        return $inline ? $ck . $label : $label . $ck;
    }

    public static function anexos($name, $value, $anexosOptions = array()) {
        $anexosOptions['name'] = $name;
        $anexosOptions['inputName'] = $name;
        $anexosOptions['value'] = $value;
        return self::widget('custom.extensions.MultipleFiles.FileUpload', array('options' => $anexosOptions));
    }

    public static function listFiles($files, $options = array()) {
        return self::widget('custom.extensions.MultipleFiles.FileList', compact('files', 'options'));
    }
    
    public static function listagem($models, $options = []) {
        $opts = array_merge( ['listFields'=>['id','id'], 'type'=>'ul'], $options );
        $out = self::openTag($opts['type']);
        $dados = self::listData($models, $opts['listFields'][0], $opts['listFields'][1]);
        foreach( $dados as $key=>$valor ){
            $out.= self::tag('li', ['dt-key'=>$key], $valor);
        }
        $out.= self::closeTag($opts['type']);
        return $out;
    }
    
    //Campo de texto que só permite dígitos
    public static function intField( $name, $value, $htmlOptions ){
        $id = $htmlOptions['id'];
        $js = "jQuery('#{$id}').mask('?9999999999999', {placeholder:''});";
        Yii::app()->clientScript->registerCoreScript('maskedinput');
        Yii::app()->clientScript->registerScript('intfilter#' . $id, $js, CClientScript::POS_END);
        return static::numberField($name, $value, $htmlOptions);
    }

}
