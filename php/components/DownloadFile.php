<?php

namespace bmelo\components;

use \yii\helpers\FileHelper;

class DownloadFile {

    protected $_file = null;
    protected $_length = null;
    protected $_mtime = null;
    protected $_begin = 0;
    protected $_end = null;
    protected $_dummy = false;
    public $force = true;
    public $filename = null;
    
    public function filesize(){
        return filesize($this->_file);
    }
    
    public function contentLenght(){
        if( $this->_length === null ){
            $end = ($this->_end !== null) ? $this->_end : ($this->filesize()-1);
            $this->_length = ($end - $this->_begin) + 1;
        }
        return $this->_length;
    }

    public function __construct($file, $filename = null, $force = true, $dummy = false) {
        if (!is_file($file) and ! $dummy) {
            throw new Exception('Arquivo não encontrado!');
        }
        $this->_file = $file;
        $this->_dummy = $dummy;
        if (!$this->_dummy) { //Arquivo não existe, será provido por conexão remota
            $this->_mtime = filemtime($file);
        }
        $this->filename = empty($filename) ? basename($file) : $filename;
        $this->normalizeFilename();
        $this->force = $force;
    }

    public function normalizeFilename() {
        $this->filename = preg_replace('/\ /', '_', $this->filename);
    }

    public static function direct($file, $filename = null, $force = true) {
        $down = new DownloadFile($file, $filename, $force);
        $down->start(); //Download controlado via PHP está dando falha com grandes arquivos
    }

    public static function directTemp($file, $filename = null, $url = '', $raiz = '/var/www', $force = true) {
        $down = new DownloadFile($file, $filename, $force);
        $down->downloadByTemp($url, $raiz); //Melhor solução para arquivos grandes, jogar controle para apache
    }

    //Alternativa para o download
    protected function _outputFile($chunkSize = 32768) {
        if ($this->_begin > 0) {
            $file = @fopen($this->_file, "rb");
            fseek($file, $this->_begin, 0); //Avança para a posição correta
            while (!feof($file) && (connection_status() == 0)) {
                print(@fread($file, $chunkSize));
                ob_flush();
                flush();
            }
        } else {
            echo readfile($this->_file);
        }
    }

    protected function writeHeader() {
        $this->checkResumeDownload(); //Inicia checando se irá ou não resumir um download já iniciado
        $disposition = $this->force ? 'attachment' : 'inline';
        header('Content-Description: File Transfer');
        header('Content-Disposition: ' . $disposition . '; filename="' . $this->filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache'); //header('Pragma: public');
        header('Accept-Ranges: bytes');
        header('Expires: 0');
        if ($this->_dummy)
            header('Content-Type: ' . FileHelper::getMimeTypeByExtension($this->_file));
        else {
            header('Content-Type: ' . FileHelper::getMimeType($this->_file));
            header("Content-Length: " . $this->contentLenght());
            $contentRange = "Content-Range: bytes {$this->_begin}-" . ($this->filesize() - 1) . "/{$this->filesize()}";
            header($contentRange);
        }
        ob_clean();
        flush();
    }

    protected function checkResumeDownload() {
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $this->_begin = intval($matches[1]);
            }
            if (isset($matches[2])) {
                $this->_end = $matches[2];
            }
        }

        $msg = ($this->_begin > 0) ? 'HTTP/1.0 206 Partial Content' : 'HTTP/1.0 200 OK';
        header($msg);
    }

    public function downloadByTemp($url = '', $path = '/var/www') {
        $fullFilePath = $path . $this->filename;
        //Verifica se é necessária alguma ação
        if (!is_file($fullFilePath) or //Se arquivo existe
            $this->_mtime > filemtime($fullFilePath)) { //Renovar o arquivo
            if (!is_dir($path)) { //Gera diretório, caso não exista
                mkdir($path, 0770, true);
            }
            //Tenta gerar link, e caso não seja possível, copia o arquivo
            $linkOk = @\symlink($this->_file, $fullFilePath);
            if ( !$linkOk && !is_file($fullFilePath) ) {
                copy($this->_file, $fullFilePath);
            }
        }
        //Joga o download para o browser
        Http::redirect( $url . $this->filename );
    }

    public function getHeader() {
        $this->writeHeader();
    }

    public function getContent() {
        $this->_outputFile();
    }

    public function start() {
        $this->writeHeader();
        $this->_outputFile();
        Yii::app()->end();
    }

}

?>
