<?php

/**
 * Description of MyActiveForm
 *
 * @author Bruno Melo <bruno.melo@idor.org>
 */
class MyActiveForm extends CActiveForm {

    public function __call($name, $parameters) {
        if (preg_match('/Complete$/', $name)) {
            $name = preg_filter('/Complete$/', '', $name);
            return $this->_Complete($name, $parameters);
        }
        return $this->_callFunction($name, $parameters);
    }

    //Atalho para gerar campo já com título e área de erros.
    protected function _Complete($func, $params) {
        $out = $this->labelEx($params[0], $params[1]);
        $out.= $this->_callFunction($func, $params);
        $out.= $this->error($params[0], $params[1]);
        return $out;
    }

    protected function _callFunction($func, $params) {
        if (method_exists('MyHtml', $func)) { //Tenta chamar função do MyHtml
            return call_user_func_array(['MyHtml', '_active'], [$func, $params]);
        }
        if (!method_exists($this, $func)) {
            throw new CException(get_class($this) . ' não possui o método ' . $func);
        }
        return call_user_func_array([$this, $func], $params);
    }

    public function fileField($model, $attribute, $htmlOptions = array()) {
        $limit = 1;
        if (isset($htmlOptions['limit'])) {
            $limit = $htmlOptions['limit'];
            unset($htmlOptions['limit']);
        }
        return parent::fileField($model, $attribute, $htmlOptions);
    }

    public function optionCk($model, $attribute, $inline = true, $htmlOptions = array()) {
        $dados = MyHtml::getDataToField($model, $attribute, $htmlOptions);
        if (isset($htmlOptions['label']))
            $dados['label'] = $htmlOptions['label'];
        return MyHtml::optionCk($dados['name'], $dados['label'], $dados['value'], $inline, $htmlOptions);
    }

    public function run() {
        parent::run();

        if (Yii::app()->request->isAjaxRequest && !empty($_POST['ajaxReturn'])) {
            $id = "#".$this->htmlOptions['id'];
            $ajaxReturn = $_POST['ajaxReturn'];
            $js = "$( '$id' ).submit( function(){ $(this).formSubmit( $ajaxReturn ); return false; });";
            Yii::app()->clientScript->registerScript('form'.$id, $js, CClientScript::POS_END);
        }
    }

}
