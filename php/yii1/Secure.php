<?php
namespace BMelo\yii1\Secure;

/**
 * Description of Secure
 *
 * @author Bruno
 */
class Secure {
    
    //Gera chave para textos
    public static function key($text){
        return sha1( $text . Yii::app()->params['hash'] );
    }
    
    //Confere se chave coincide
    public static function validate( $text, $key ){
        return ( static::key($text) === $key );
    }
}
