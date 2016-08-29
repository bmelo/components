<?php

/**
 * Description of MyYii
 * @author Bruno Melo <bruno.melo@idor.org>
 */
class MyYii {

  static protected $_initialized = false;
  static public $baseUrl = '';
  static public $basePath = '';

  static protected function baseScriptUrl() {
    if (!self::$_initialized) {
      self::$basePath = dirname(__FILE__).'/assets';
      self::$baseUrl = Yii::app()->getAssetManager()->publish(self::$basePath, false, -1, YII_DEBUG);
      self::$_initialized = true;
    }
    return self::$baseUrl;
  }

  public static function registerCss($css) {
    $file = self::baseScriptUrl() . $css;
    Yii::app()->clientScript->registerCssFile($file);
  }

  public static function registerJs($js) {
    $file = self::baseScriptUrl() . $js;
    Yii::app()->clientScript->registerScriptFile($file);
  }

}
