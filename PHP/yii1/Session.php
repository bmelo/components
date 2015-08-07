<?php

/**
 * Description of Session
 *
 * @author Bruno
 */
class Session {

    //Salva os últimos n registros na sessão, permitindo retorno para URL navegada anteriormente
    public static function updateUrls($n = 10) {
        $urls = Yii::app()->session['session'];
        if (!is_array($urls)) {
            $urls = [];
        }
        $curUrl = Yii::app()->request->url;
        if ($curUrl === end($urls)) {
            return;
        }
        $urls[] = $curUrl;
        Yii::app()->session['session'] = array_slice($urls, -$n, $n);
    }

    //Redireciona para páginas do histórico
    public static function backUrl($npos = 1) {
        $url = self::getLastUrl($npos);
        if (empty($url)) { //Fica na página atual
            $url = Yii::app()->request->urlReferrer;
        }
        Yii::app()->controller->redirect($url);
    }

    //Url de retorno
    public static function getLastUrl($npos = 1) {
        $urls = Yii::app()->session['session'];
        if ( empty($urls) || $pos < 1 ) {
            return null;
        }
        $ignoreFirst = ($urls[0] == Yii::app()->request->url);
        $pos = ($npos - 1) + $ignoreFirst;
        
        return $urls[$pos];
    }

}
