<?php

namespace bmelo\yii2\assets\bower;

use yii\web\AssetBundle;

class MomentAsset extends AssetBundle 
{
    public $sourcePath = '@bower/moment'; 
    public $js = [ 
        'moment.js', 
    ];
    public $publishOptions = [
        'only' => [
            'min/',
            'moment.js',
        ]
    ];
}