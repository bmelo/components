<?php

/**
 * Description of MyHtml
 *
 * @author Infomed
 */
class Thumb {

  public static function thumbnize($img, $h = 150, $w = 150, $dest = 'webroot.images.thumbs', $force = false) {
    $name = basename($img);
    $thumbName = $h . '_' . $w . '_' . $name;
    $dest = Yii::getPathOfAlias($dest) . '/' . $thumbName;
    if ($force or !file_exists($dest)) {
      $img = Yii::app()->image->load($img);
      //$img2 = $img;
      //$img2->resize(120, 120)->quality(85)->save();
      //$img->resize(280, 280)->crop(200,200);
      $img->resize($h, $w)->quality(83)->save($dest);
    }
    return $thumbName;
  }
}

?>