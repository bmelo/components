<?php
namespace bmelo\yii2\widgets;

use yii\bootstrap\Modal;
use yii\bootstrap\Html;

/**
 * Description of ModalAjax
 *
 * @author bruno.melo
 */
class ModalAjax extends Modal{
    
    /**
     * Initializes the widget.
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        
        $this->initOptions();

        echo $this->renderToggleButton() . "\n";
        echo Html::beginTag('div', ['class' => 'modal-dialog ' . $this->size]) . "\n";
        echo Html::beginTag('div', ['class' => 'modal-content']) . "\n";
        echo $this->renderHeader() . "\n";
        echo $this->renderBodyBegin() . "\n";
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo "\n" . $this->renderBodyEnd();
        echo "\n" . $this->renderFooter();
        echo "\n" . Html::endTag('div'); // modal-content
        echo "\n" . Html::endTag('div'); // modal-dialog

        $this->registerPlugin('modal');
    }
    
}
