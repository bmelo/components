<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator bmelo\yii2\generators\module\Generator */

?>
<div class="module-form">
<?php
    echo $form->field($generator, 'moduleClass');
    echo $form->field($generator, 'moduleID');
?>
</div>
