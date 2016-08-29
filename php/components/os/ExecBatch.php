<?php

namespace bmelo\components\os;

/**
 * Description of ExecBatch
 *
 * @author bmelo
 */
class ExecBatch {

    public $conversor = '';
    public $outDir = '';
    private $cmds = array();
    private $tmpBatName;

    private function isWindows() {
        return strtoupper(substr(PHP_OS, 0, 3)) == "WIN";
    }

    private function glueCmds() {
        if ($this->isWindows()) {
            return " & ";
        }
        return " ; ";
    }

    //For Windows
    //Gera .bat temporário com todos os comandos a serem processados
    private function tempBat() {
        if( !$this->isWindows() ){
            return false;
        }
        $tmpfname = tempnam(sys_get_temp_dir(), 'BAT');
        $this->tmpBatName = $tmpfname . '.bat';
        rename($tmpfname, $this->tmpBatName);
        $handle = fopen($this->tmpBatName, "w");
        foreach ($this->cmds as $cmd) {
            fwrite($handle, $cmd . "\r\n");
        }
        fwrite($handle, "DEL \"%~f0\""); // Para excluir o arquivo ao terminar execução
        fclose($handle);
        return $this->tmpBatName;
    }

    public function exec() {
        foreach ($this->cmds as $cmd) {
            exec($cmd);
        }
    }

    //Executa comandos sem bloquear o processo
    public function execBg() {
        if ($this->isWindows()) {
            $tmpbat = $this->tempBat();
            pclose(popen("start /b cmd /c call {$tmpbat} >NUL 2>&1", "r"));
        } 
        //UNIX based system
        else{
            foreach( $this->cmds as $cmd ){
                exec($cmd . " > /dev/null &");
            }
        }
    }

    function execBg2() {
        $tmpBat = $this->tempBat();
        $WshShell = new COM("WScript.Shell");
        $oExec = $WshShell->Run($tmpBat, 0, false);
        return $oExec == 0 ? true : false;
    }

    public function addCmd($cmd) {
        $this->cmds[] = $cmd;
        return $this;
    }

    public function reset() {
        $this->cmds = array();
    }

}