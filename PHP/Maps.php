<?php

/**
 * Description of MyHtml
 *
 * @author Infomed
 */
class Maps {

  public static $url = 'http://maps.googleapis.com/maps/api/staticmap';
  private static $default = array(
      'zoom' => '15',
      'sensor' => 'true',
      'maptype' => 'hybrid',
      'size' => '300x300'
  );

  /**
   * @param GMarker $marker Marcador a ser utilizado
   */
  public static function imgMarker($marker, $options = array(), $imgOptions = array()) {
    $options = array_merge($options, self::$default);
    $params = http_build_query(array('markers' => $marker->str()) + $options);
    return CHtml::image(self::$url . '?' . $params, '', $imgOptions);
  }

  //PARAMS http://asnsblues.blogspot.com.br/2011/11/google-maps-query-string-parameters.html
  public static function link($texto, $marker, $options = array()) {
    $params = array(
        'q' => $marker->latitude.'%2C+'.$marker->longitude,
        't' => 'h', //tipo de mapa
        'z' => '16', //zoom
        'll' => $marker->latitude.','.$marker->longitude,
    );
    $url = 'http://maps.google.com/?'.http_build_query($params);
    return CHtml::link( $texto, $url, array('target' => '_blank') );
  }

}
