<?php

namespace bmelo\components;

/**
 * Description of String
 *
 * @author bmelo
 */
class Texto {

    public static function removeAcentos($text, $underscore = false) {
        $text = trim( $text );
        $text = preg_replace('/[\,`^~\'":!-\?]/', null, iconv('UTF-8', 'ASCII//TRANSLIT', $text));
        $text = preg_replace('/\s{2,}/', ' ', $text);
        if ($underscore) {
            $text = preg_replace('/\s/', '_', $text);
        }
        return $text;
    }

    public static function onlyNumbers($text) {
        return preg_replace('/\D/', null, $text);
    }

    public static function formatFilenameWeb($text) {
        $text = self::removeAcentos(strtolower($text));
        $text = preg_replace('/[^\w-\[\]\.]/', ' ', $text);
        return preg_replace('/ +/', '_', trim($text));
    }

}
