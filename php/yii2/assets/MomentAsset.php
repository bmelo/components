<?php

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

class MomentAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/moment';
    /**
     * @var array
     */
    public $js = [
        'moment.js'
    ];

    /**
     * @inheritdoc
     * @param \yii\web\View $view
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $view->registerJs('moment.createFromInputFallback = function(config) {
            config._d = new Date(config._i);
        };');
    }
}
