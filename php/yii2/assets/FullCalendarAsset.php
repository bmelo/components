<?php
/**
 * @copyright Copyright &copy; Thiago Talma, thiagomt.com, 2014
 * @package yii2-fullcalendar-widget
 * @version 1.0.0
 */

namespace bmelo\yii2\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for FullCalendar
 *
 * @author Thiago Talma <thiago@thiagomt.com>
 * @since 1.0
 */
class FullCalendarAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/fullcalendar/dist';
    /**
     * @inheritdoc
     */
    public $js = [
        'fullcalendar.js'
    ];
    /**
     * @inheritdoc
     */
    public $css = [
        'fullcalendar.css'
    ];
    
    public $publishOptions = [
        'only' => [
            'fullcalendar*',
            'lang/pt-br.js',
        ],
        'forceCopy' => YII_ENV_DEV,
    ];
    
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\jui\JuiAsset',
        'bmelo\yii2\assets\MomentAsset',
    ];
}
