<?php

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

class PrintAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/fullcalendar/dist';
    /**
     * @var array
     */
    public $css = [
        'fullcalendar.print.css'
    ];
    /**
     * @var array
     */
    public $cssOptions = [
    	'media' => 'print'
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'bmelo\yii2\assets\FullCalendarAsset',
    ];
}

