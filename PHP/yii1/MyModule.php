<?php

/**
 * Description of IM_Module
 *
 * @author Bruno
 */
class MyModule extends CWebModule {

  public $name = null;
  protected $_assetsUrl = null;
  protected $_include = [];

  public function init() {
    $this->setImport(array(
      'custom.components.*',
      'custom.extensions.quickdlgs.*',
    ));
    Yii::setPathOfAlias('extModule', Yii::getPathOfAlias('custom.extensions'));
  }

  public function getAssetsUrl() {
    if ($this->_assetsUrl == null) {
      $this->_assetsUrl = Yii::app()->assetManager->publish(
          Yii::getPathOfAlias("{$this->id}.assets"), false, -1, RELOAD_ASSETS
      );
    }
    return $this->_assetsUrl;
  }

  public function beforeControllerAction($controller, $action) {
    $controller->menu = array();    
    return parent::beforeControllerAction($controller, $action);
  }
  
  public function getControllerPath($controller = null) {
    if( $controller!=null ){
      return parent::getControllerPath() . DS .ucfirst($controller).'.php';
    }
    return parent::getControllerPath();
  }

  public function checkIncludes($controller) {
    echo $this->getControllerPath($controller); exit;
    if( !is_file($this->getControllerPath($controller)) ){
      echo $this->getControllerPath($controller); exit;
    }
  }

}
