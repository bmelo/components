<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of String
 *
 * @author bmelo
 */
class Texto {
  public static function removeAcentos( $text ){
//    return preg_replace( '/[`^~\'"]/', null, $text );
    return preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', $text ) );
  }
  
  public static function onlyNumbers( $text ){
    return preg_replace( '/\D/', null, $text );
  }
}
