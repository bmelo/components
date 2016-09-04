<?php

namespace bmelo\components;

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
  
  public static function formatFilenameWeb( $text ){
      $text = self::removeAcentos(strtolower($text));
      $text = preg_replace('/[^\w-\[\]\.]/',' ',$text);
      return preg_replace('/ +/','_', trim($text) );
  }
}
