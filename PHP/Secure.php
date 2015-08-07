<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
