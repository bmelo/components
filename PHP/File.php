<?php
/**
 * Description of File
 *
 * @author Bruno
 */
class File extends CFileHelper{
    
    public static function createFile( $filename, $content = NULL ){
        $dir = dirname($filename);
        if( !is_dir($dir) ){ //Cria o diretório se ainda não existir
            static::createDirectory($dir);
        }
        return file_put_contents($filename, $content);
    }
    
    public static function fopen($filename, $mode){
        if( !is_file($filename) ){
            static::createFile($filename);
        }
        return fopen($filename, $mode);
    }
}
