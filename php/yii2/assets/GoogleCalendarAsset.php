<?php

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

class GoogleCalendarAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/fullcalendar/dist';
    /**
     * @inheritdoc
     */
    public $js = [
        'gcal.js'
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'bmelo\yii2\assets\FullCalendarAsset',
    ];
}