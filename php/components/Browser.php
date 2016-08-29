<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace bmelo\components;

use Yii;

/**
 * Description of Browser
 *
 * @author bruno
 */
class Browser {
    public static function getIP(){
        if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ){
            $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            return $ips[0];
        }
        return Yii::$app->request->userIP;
    }
    
    
    public static function detect( $ua = null ){
        $uaParser = new \UAParser\UAParser();
        $ua = ( $ua === null ) ? $_SERVER['HTTP_USER_AGENT'] : $ua;
        
        return static::ParseToArray( $uaParser->parse($ua) );
    }
    
    /**
     * @param  \UAParser\Result\ResultFactory $result
     * @return array
     */
    protected static function ParseToArray( $result ){
        $os = $result->getOperatingSystem();
        $browser = $result->getBrowser();
        $device = $result->getDevice();
        
        return [
            'OS' => [
                'family' => $os->getFamily(),
                'major'  => $os->getMajor(),
                'minor'  => $os->getMinor(),
                'patch'  => $os->getPatch(),
            ],
            'Browser' => [
                'family'  => $browser->getFamily(),
                'major'   => $browser->getMajor(),
                'minor'   => $browser->getMinor(),
                'patch'   => $browser->getPatch(),
                'version' => $browser->getVersionString(),
            ],
            'Device' => [
                'constructor' => $device->getConstructor(),
                'isMobile'    => $device->isMobile(),
                'isTablet'    => $device->isTablet(),
                'isDesktop'   => $device->isDesktop(),
                //'isBot'       => $device->isMobile(),
            ]
        ];
    }
}
