<?php

/**
 * Description of dbRemote
 *
 * @author Bruno <bruno.raphael@gmail.com>
 */
Yii::import('custom.components.db.*');

class DbRemote extends CDbConnection {

    public $key;

    protected function open() {
        
    }

    protected function getUrl() {
        $pos = strpos($this->connectionString, ':') + 1;
        return substr($this->connectionString, $pos);
    }

    public function createCommand($query = null) {
        $this->setActive(true);
        return new RemoteDbCommand($this, $query, $this->getUrl(), $this->key);
    }

    public function getLastInsertID($sequenceName = '') {
        $id = NULL;
        $fileid = Yii::getPathOfAlias('application.runtime.db.lastinsertid')."-{$sequenceName}.data";
        if( is_file($fileid) ){
            $id = file_get_contents($fileid);
            unlink($fileid);
        }
        return $id;
    }

}
