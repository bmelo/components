<?php
namespace BMelo;
/**
 * Description of Server
 *
 * @author Bruno RP Melo
 */
class Server {

    public static function isWin() {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    public static function getIp() {
        if( self::isWin() ){
            $info = shell_exec('ipconfig /all');
            preg_match('/IPv4\D*((\d{1,3}\.*){4})/', $info, $matches);
            $ip = $matches[1];
        }else{
            $ip = shell_exec("ifconfig  | grep 'inet ' | grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'");
        }
        return trim($ip);
    }

}
