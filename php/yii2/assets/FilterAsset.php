<?php

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

class FilterAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/bmelo/components/php/yii2/assets';
    /**
     * @var array
     */
    public $css = [
        'css/filters.css'
    ];
}

