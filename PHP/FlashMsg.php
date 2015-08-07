<?php

/**
 * Description of FlashMsg
 *
 * @author Bruno
 */
class FlashMsg {

  private $tipos = array(
      'success' => 'SUCESSO',
      'info' => 'INFO',
      'warning' => 'WARNING',
      'error' => 'ERRO',
      'danger' => 'PERIGO'
  );
  private $tipo = '', $msg = '';

  public function __construct( $tipo, $msg, $set = true ) {
    $this->tipo = $tipo;
    $this->msg = $msg;
    if($set)
      $this->setFlash();
  }
  
  public function msg(){
    return "<b>{$this->tipos[$this->tipo]}!</b> " . $this->msg;
  }
  
  public function setFlash(){
    Yii::app()->user->setFlash($this->tipo, $this->msg());
  }
}
