<?php

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

    //Gera .bat temporário com todos os comandos a serem processados
    private function tempBat() {
        $tmpfname = tempnam(sys_get_temp_dir(), 'BAT');
        $this->tmpBatName = $tmpfname . '.bat';
        rename($tmpfname, $this->tmpBatName);
        $handle = fopen($this->tmpBatName, "w");
        foreach ($this->cmds as $cmd) {
            fwrite($handle, $cmd . "\r\n");
        }
        fwrite($handle, "DEL \"%~f0\""); // Para excluir o arquivo ao terminar execução
        fclose($handle);
        Yii::log($this->tmpBatName);
        return $this->tmpBatName;
    }

    public function exec() {
        foreach ($this->cmds as $cmd) {
            exec($cmd);
        }
    }

    //Executa comandos sem bloquear o processo
    public function execBg() {
        $tmpbat = $this->tempBat();
        if ($this->isWindows()) {
            pclose(popen("start /b cmd /c call {$tmpbat} >NUL 2>&1", "r"));
        } else
            exec($this->cmds . " > /dev/null &"); // - Para linux
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
