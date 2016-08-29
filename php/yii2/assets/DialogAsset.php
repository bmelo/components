<?php
/**
 * @copyright Copyright &copy; Thiago Talma, thiagomt.com, 2014
 * @package yii2-fullcalendar-widget
 * @version 1.0.0
 */

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Dialog
 *
 * @author Bruno Melo <bruno.melo@gmail.com>
 * @since 1.0
 */
class DialogAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/bmelo/components/php/yii2/assets';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/tools.js',
        'js/dialog-extension.js'
    ];
    /**
     * @inheritdoc
     */
    public $css = [
        'css/dialog-extension.css'
    ];
    
    public $publishOptions = [
        'only' => [
            'js/*',
            'css/*',
        ],
        'forceCopy' => YII_ENV_DEV,
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}
