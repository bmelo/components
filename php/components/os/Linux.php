<?php

namespace bmelo\components\os;

/**
 * Helper for Linux actions
 *
 * @author bruno.melo
 */
class Linux {

    /**
     * Returns the UID to the project
     * @param Projeto $prj
     * @return int
     */
    public static function getUID($prj) {
        return $prj->id + 700;
    }

    /**
     * 
     * @param Projeto $prj
     */
    public static function creatLinuxGroup($prj, $laminas) {
        $laminas = CHtml::listData($laminas, 'id', 'ip');
        $cmd = "groupadd -g {$prj->uid} {$prj->prjid}";
        Blade::runCommand($cmd, $laminas, true, false);
    }

    /**
     * Changing group master of project's directory
     * @param Projeto $prj
     */
    public static function putPermissionDir($prj, $laminas) {
        $dir = $prj->getPathDir(true);
        if ($dir) {
            $cmd = "chown idor:{$prj->uid} {$dir}";
            $laminas = CHtml::listData($laminas, 'id', 'ip');
            Blade::runCommand($cmd, $laminas, true, false);
        }
    }

    public static function addUserGroup($userid, $groupid) {
        $laminas = CHtml::listData(Lamina::model()->findAll(), 'id', 'ip');
        $cmd = "gpasswd -a {$userid} {$groupid}";
        Blade::runCommand($cmd, $laminas, true, false);
}

    public static function rmUserGroup($userid, $groupid) {
        $laminas = CHtml::listData(Lamina::model()->findAll(), 'id', 'ip');
        $cmd = "gpasswd -d {$userid} {$groupid}";
        Blade::runCommand($cmd, $laminas, true, false);
    }
    
    public static function rm( $lamina, $file ){
        $cmd = "rm -f $file";
        Blade::runCommand($cmd, $lamina, true, false);
    }
    
    public static function ln( $lamina, $file, $link ){
        $cmd = "ln -s $file $link";
        Blade::runCommand($cmd, $lamina, true, false);
    }

}
