<?php

namespace bmelo\components\os;

/**
 * Description of Terminal
 *
 * @author bruno.melo
 */
class Terminal {
    protected $_cmds = []; //To put multiple lines of commands
    protected $_toFile = false;
    protected $_outFile = null;
    protected $_autoExclude = true;
    protected $_outputs = [];
    
    public function __construct( $cmd = null ){
        $this->add( $cmd );
    }
    
    public function add( $cmd ){
        if( !empty($cmd) ){
            $this->_cmds[] = $cmd;
        }
        return $this;
    }
    
    public function mergeInFile( $opt, $fileDest = null, $autoExclude = true ){
        $this->_toFile = (bool) $opt;
        $this->fileDest( $fileDest );
        $this->autoEclude($autoExclude);
        return $this;
    }
    
    public function autoExclude( $exclude ){
        if( $exclude !== null ){
            $this->_autoExclude = $exclude;
        }
    }
    
    protected function _fileDest( $fileDest ){
        if( $fileDest!== null ){
            $this->_outFile = (string) $fileDest;
        }
    }
    
    public function createFileCommands( $fileDest = null, $autoExclude = null ){
        $this->_fileDest( $fileDest );
        $this->autoExclude( $autoExclude );
        //Checa se usa nome temporário ou o que foi especificado
        if( empty($this->_outFile) ){
            $this->_outFile = tempnam(sys_get_temp_dir(), 'IM');
        }
        $this->_putCmdsFile();
        $this->_cmds = [];
        return $this;
    }
    
    protected function _putCmdsFile(){
        $eol = Server::isWin() ? "\r\n" : "\n";
        $autoExclude = Server::isWin() ? "DEL \"%~f0\"" : "rm -- \"$0\"";
        $handle = fopen($this->_outFile, "w");
        foreach ($this->_cmds as $cmd) {
          fwrite($handle, $cmd . $eol);
        }
        if( $this->_autoExclude ){
            fwrite($handle, $autoExclude); // Para excluir o arquivo ao terminar execução
        }
        fclose($handle);
        chmod($this->_outFile, 0111);
    }
    
    protected function getCmd(){
        if( $this->_toFile ){
            $this->createFileCommands();
            return $this->_outFile;
        }else{
            $glue = Server::isWin() ? "&" : ";";
            return implode( " {$glue} ", $this->_cmds);
        }
    }
    
    public function getOutputs(){
        return $this->_outputs;
    }

    public function exec(){
        if( Server::isWin() ){
            $out = shell_exec( $this->getCmd() );
        }else{
            $out = shell_exec( $this->getCmd()." 2>&1" );
        }
        $this->_outputs[] = $out;
        $this->_cmds = [];
        return $this;
    }
    
    public function execBg(){
        if( Server::isWin() ){
            $cmd = $this->_toFile ? "start /b {$this->getCmd()}" : $this->getCmd();
            pclose( popen($cmd, "r") );
        }else{
            shell_exec( $this->getCmd()." > /dev/null &" );
        }
        $this->_cmds = [];
        return $this;
    }
}
