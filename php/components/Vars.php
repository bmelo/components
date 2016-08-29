<?php

namespace bmelo\components;

class Vars {

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

    /**
     * Checks if all elements in a array are empty
     * @param array $arr
     * @param string $item
     * @return boolean
     */
    static function allEmpty(&$arr, $item = null) {
        if ($item !== null) {
            if (!isset($arr[$item])) {
                return true;
            }
            $items = $arr[$item];
        } else {
            $items = $arr;
        }
        foreach ($items as $item) {
            if (!empty($item)) {
                return false;
            }
        }
        return true;
    }

}

?>
