<?php

namespace BMelo\Foto;

class Foto extends CActiveRecord {

    protected $filename = '';

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public static function checkFoto($file) {
        if (is_file($file)) {
            list($width, $height, $type, $attr) = getimagesize($file);
            if ($type) {
                return true;
            }
        }
        return false;
    }

    public static function extractGPS($info) {
        if (empty($info['GPSLatitudeRef'])) {
            return false;
        }
        $lon = $info['GPSLongitude'];
        $lat = $info['GPSLatitude'];
        $lonR = $info['GPSLongitudeRef'];
        $latR = $info['GPSLatitudeRef'];
        $posGPS = new GMarker;
        $posGPS->setLatitude($lat[0], $lat[1], $lat[2], $latR);
        $posGPS->setLongitude($lon[0], $lon[1], $lon[2], $lonR);
        return $posGPS;
    }

    function load() {
        return Yii::app()->image->load($this->filename);
    }

    function setFile($filename) {
        $this->filename = $filename;
    }

    final private static function createDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, '0777', true);
        }
    }

    final public static function resizeFolder($pasta, $w, $h, $q, $dest = 'resized') {
        $fotos = array();
        $arqs = scandir($pasta);
        foreach ($arqs as $arq) {
            $filename = $pasta . DIRECTORY_SEPARATOR . $arq;
            if (!empty($dest)) {
                $resized = $pasta . DIRECTORY_SEPARATOR . $dest . DIRECTORY_SEPARATOR . $arq;
            } else {
                $resized = $filename;
            }
            if (!Foto::checkFoto($filename)) {
                continue;
            }
            $before = exif_read_data($filename);
            $before['filename'] = $filename;
            $img = Yii::app()->image->load($filename)->resize($w, $h)->quality($q);
            self::createDir(dirname($resized));
            $img->save($resized);
            $after = exif_read_data($resized);
            $after['filename'] = $resized;
            $fotos[] = array('before' => $before, 'after' => $after);
        }
        return $fotos;
    }

}
