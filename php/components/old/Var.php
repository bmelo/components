<?php

namespace BMelo;

class CVar {

    static function toArray(&$var) {
        if (!is_array($var))
            $var = [$var];
        return $var;
    }

    static function getItemArr(&$arr, $item, $default = null) {
        if (!isset($arr[$item]))
            $arr[$item] = $default;
        return $arr[$item];
    }

}

?>
