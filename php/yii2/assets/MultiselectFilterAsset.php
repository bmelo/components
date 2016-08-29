<?php

namespace bmelo\yii2\assets;

/**
 * Description of FixMultiselect
 *
 * @author bruno.melo
 */
class MultiselectFilterAsset extends \yii\web\AssetBundle {

    public $sourcePath = '@vendor/bmelo/components/php/yii2/assets';
    
    public $js = [
        'js/yii.gridView.fix.js'
    ];
    
    public $depends = [
        'bmelo\yii2\assets\FilterAsset'
    ];

}
