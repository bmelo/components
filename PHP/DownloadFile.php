<?php

class DownloadFile {

  private $_file = null;
  private $_size = null;
  private $_mtime = null;
  private $_begin = 0;
  private $_end = null;
  private $_dummy = false;
  public $force = true;
  public $filename = null;

  public function __construct($file, $filename = null, $force = true, $dummy = false) {
    if (!is_file($file) and ! $dummy) {
      throw new CException('Arquivo não encontrado!');
    }
    $this->_file = $file;
    $this->_dummy = $dummy;
    if (!$this->_dummy) { //Arquivo não existe, será provido por conexão remota
      $this->_mtime = filemtime($file);
      $this->_size = filesize($file);
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
  
  public static function directTemp($file, $filename = null, $raiz = '/temp/', $force = true) {
    $down = new DownloadFile($file, $filename, $force);
    $down->downloadByTemp($raiz); //Melhor solução para arquivos grandes, jogar controle para apache
  }

  //Alternativa para o download
  private function _outputFile($chunkSize = 32768) {
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

  private function writeHeader() {
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
      header('Content-Type: ' . CFileHelper::getMimeTypeByExtension($this->_file));
    else {
      header('Content-Type: ' . CFileHelper::getMimeType($this->_file));
      header("Content-Length: " . ($this->_size - $this->_begin));
      $contentRange = "Content-Range: bytes {$this->_begin}-" . ($this->_size - 1) . "/{$this->_size}";
      header($contentRange);
    }
    ob_clean();
    flush();
  }

  private function checkResumeDownload() {
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

  public function downloadByTemp($raiz = '/temp/download/') {
    $path = Yii::getPathOfAlias('webroot') . $raiz; //Pasta raiz para armazenar o download
    $fullFilePath = $path . $this->filename;
    //Verifica se é necessária alguma ação
    if (!is_file($fullFilePath) or //Se arquivo existe
        $this->_mtime > filemtime($fullFilePath)) { //Renovar o arquivo
      if (!is_dir($path)) { //Gera diretório, caso não exista
        mkdir($path, 0777, true);
      }
      copy($this->_file, $fullFilePath);
    }
    //Joga o download para o browser
    Yii::app()->controller->redirect(Yii::app()->baseUrl . $raiz . $this->filename);
    Yii::app()->end();
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
