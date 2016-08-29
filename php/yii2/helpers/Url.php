<?php

namespace bmelo\yii2\helpers;

use Yii;
use yii\helpers\BaseUrl;

/**
 * Description of Session
 *
 * @author Bruno
 */
class Url extends BaseUrl {

    const SESSION_URLS = 'urlsRemembered';

    //Salva os últimos n registros na sessão, permitindo retorno para URL navegada anteriormente
    public static function rememberStack($n = 10) {
        $urls = Yii::$app->session[self::SESSION_URLS];
        if (!is_array($urls)) {
            $urls = [];
        }
        $curUrl = self::current();
        if ($curUrl === end($urls)) {
            return;
        }
        $urls[] = $curUrl;
        Yii::$app->session[self::SESSION_URLS] = array_slice($urls, -$n, $n);
    }

    //Redireciona para páginas do histórico
    public static function goBack($npos = 1) {
        $url = self::getLastUrl($npos);
        if (empty($url)) { //Fica na página atual
            $url = Yii::$app->getRequest()->referrer;
        }
        if (Yii::$app->getRequest()->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\helpers\Json::encode( [ 'returnUrl'=>$url ]);
        } else {
            Yii::$app->controller->redirect($url);
        }
    }

    //Url de retorno
    public static function getLastUrl($npos = 1) {
        $urls = Yii::$app->session[self::SESSION_URLS];
        if (empty($urls) || $npos < 1) {
            return null;
        }
        $ignoreFirst = ( end($urls) == self::current() );
        $pos = count($urls) - 2 + $npos - $ignoreFirst;
        
        //Checking if has something to return
        if( $pos >= 0 ){
            return $urls[$pos];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function remember($url = '', $name = null) {
        if ($name === null) {
            self::rememberStack();
        } else {
            parent::remember($url, $name);
        }
    }

    public static function previous($name = null) {
        if ($name === null) {
            return self::getLastUrl();
        }
        return parent::previous($name);
    }

}
