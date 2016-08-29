<?php

namespace bmelo\components\os;

use Yii;

/**
 * Description of Blade
 *
 * @author bruno.melo
 */
class Blade {

    const SERVER = '10.36.4.206';
    const PORT = 39001;

    protected static $_binDir;

    public static function binDir() {
        if (empty(static::$_binDir)) {
            static::$_binDir = __DIR__ . '/../../../bin';
        }
        return static::$_binDir;
    }

    protected static function sendMsg($fp, $msg) {
        fwrite($fp, $msg);
        while (!feof($fp)) {
            return fgets($fp, 2048);
        }
    }

    static protected function openDirect($ip, $lamina, $program, $user, $root) {
        $cmd = "export DISPLAY={$ip}; {$program}";
        if (!$root) {
            $cmd = "runuser -l $user -c '{$cmd}'";
        }
        $sys_exec = "ssh -YC -A -o ServerAliveInterval=100 root@{$lamina} \"{$cmd}\"";
        shell_exec("$sys_exec > /dev/null &");
    }

    static function openProgram($ip, $lamina, $cmd, $user, $root) {
        if (!Server::isWin()) {
            return self::openDirect($ip, $lamina, $cmd, $user, $root);
        }
        //Caso do windows
        $fp = fsockopen(self::SERVER, self::PORT, $errno, $errstr, 30);
        if (!$fp) {
            throw new CException("$errstr ($errno)", 'blade');
        } else {
            self::sendMsg($fp, "HELLO"); //Opening connection
            //Opening program
            $msgProgram = sprintf("OPEN %s && %s && %s && %s && %s", $ip, $lamina, $cmd, $user, $root);
            self::sendMsg($fp, $msgProgram);
            fclose($fp);
        }
    }

    //For UNIX system
    static private function runLamina($cmd, $lamina, $background = false) {
        $action = $background ? 'exec' : 'execBg';
        $cmdTerm = "ssh root@{$lamina} \"{$cmd}\"";

        (new Terminal())->add($cmdTerm)->$action();
    }

    //For Windows
    static private function runPlink($cmd, $lamina, $background = false) {
        $user = 'root';
        $plink = Yii::getAlias('@vendor/idor/plugins/bin/putty/plink.exe');
        $key = Yii::getAlias('@vendor/idor/plugins/bin/putty/chaves/private_key_root.ppk');
        $exec = new ExecBatch();
        $cmd = "{$plink} -X -A -l {$user} -i {$key} {$lamina} \"{$cmd}\"";
        if ($background)
            $exec->addCmd($cmd)->execBg();
        else
            $exec->addCmd($cmd)->exec();
    }

    //Executa um determinado comando nas lâminas especificadas, seguindo o SO
    static function run($cmd, $lamina, $background = true) {
        if (Server::isWin()) {
            self::runPlink($cmd, $lamina, $background);
        } else {
            self::runLamina($cmd, $lamina, $background);
        }
    }

    //Executa um determinado comando nas lâminas especificadas
    static function runCommand($cmd, $laminas, $background = true) {
        foreach ( (array) $laminas as $lamina ) {
            self::run($cmd, $lamina, $background);
        }
    }

}
