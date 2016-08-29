<?php

/**
 * Description of MyHtml
 *
 * @author Infomed
 */
class GMarker {

  public $latitude, $longitude;

  function __construct($latitude = null, $longitude = null) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
  }
  
  function setLatitude( $deg, $min, $sec, $hem ){
    $this->latitude = self::toDecimal($deg, $min, $sec, $hem);
  }
  
  function setLongitude( $deg, $min, $sec, $hem ){
    $this->longitude = self::toDecimal($deg, $min, $sec, $hem);
  }

  private static function solve($str) {
    eval('$result = ' . $str . ';');
    return $result;
  }

  private static function toDecimal($deg, $min, $sec, $hem) {
    $deg = self::solve($deg);
    $min = self::solve($min);
    $sec = self::solve($sec);
    //$d = $deg + ((($min / 60) + ($sec / 3600)) / 100);
    $d = $deg + (($min / 60) + ($sec / 3600)); //Ajuste para funcionar para a camera da NIKON
    return ($hem == 'S' || $hem == 'W') ? $d*=-1 : $d;
  }

  private static function toGoogle($deg, $min, $sec, $hem) {
    $deg = self::solve($deg);
    $min = self::solve($min);
    $deg = ($hem == 'S' || $hem == 'W') ? $deg*=-1 : $deg;
    return sprintf('%d°+%f', $deg, $min);
  }

  function str() {
    return $this->latitude.','.$this->longitude;
  }

}