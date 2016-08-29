<?php

class Helper {
    static $count = 0;
    static function filterDialog($data) {
        $id = 'HfilterDialog_' . ++Helper::$count;
        $filter = CHtml::image(Yii::app()->baseUrl . '/images/icons/filter_32.png', 'filtrar', array(
              'style' => 'cursor:pointer;',
              'onclick' => "$('.dlgHelFilter div:not(#$id)').slideUp(); $('#$id').slideToggle();",
        ));
        $filter .= "<div id='" . $id . "'>" . $data . "</div>";
        return '<div class="dlgHelFilter">' . $filter . '</div>';
    }

    static function compareDateRange($criteria, $campo, $value) {
        if (empty($value)) {
            return false;
        }
        $dts = explode('-', urldecode($value));
        $criteria->compare($campo, '>' . DateTools::formatDbDate( $dts[0] ) . ' 00:00:00', true);
        $criteria->compare($campo, '<' . DateTools::formatDbDate( $dts[1] ) . ' 23:59:59', true);
    }

    //Data no formato brasileiro
    static function getTimestamp($data) {
        return DateTools::getTimestamp($data);
    }

    static function dataBr($date, $short = true) {
        return DateTools::dataBr($date, $short);
    }

    static function formatDate($data, $formatOr = 'dd/MM/yyyy hh:mm:ss', $formatDest = 'Y-m-d H:i:s') {
        return DateTools::formatDate($data, $formatOr, $formatDest);
    }

    static function formatDbDate($data) {
        return DateTools::formatDbDate($data);
    }
    
    static function requestUrlBg($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
        curl_exec($ch);
        curl_close($ch);
    }

    static function wgetBg( $url ){
        $vendordir = Yii::getPathOfAlias('custom.vendors');
        $command = "{$vendordir}/wget/wget.exe {$url} -q -O - -b";
        $proc = popen($command, "r");
        pclose($proc);
    }

    static function ajaxRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_PROXY, 'http://gw01.rededor.com.br:8080');
        //curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'marco.conrado:P!csdor11');
        $chData = curl_exec($ch);
        curl_close($ch);
        echo $chData;
    }

    static function get($name, $default = null, $allowEmpty = true) {
        $valor = $default;
        if (isset($_POST[$name])) {
            $valor = $_POST[$name];
        } elseif (isset($_GET[$name])) {
            $valor = $_GET[$name];
        }
        if (empty($valor) and ! $allowEmpty) {
            throw new CHttpException('400', 'Requisição inválida.');
        }
        if (is_string($valor) AND ( $valor === 'true' or $valor === 'false')) {
            $valor = $valor === 'true' ? true : false;
        }
        return $valor;
    }

    static public function getPartQuery($url, $part = '') {
        $partsUrl = parse_url($url);
        $query = null;
        if (!empty($partsUrl['query'])) {
            parse_str($partsUrl['query'], $query);
            if (!empty($part))
                return $query[$part];
        }
        return $query;
    }

    //Converte de uma unidade maior para bytes
    static public function toBytes($size, $unit) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = array_search($unit, $units);

        return round($size * pow(1024, $pow));
    }

    //Converte de uma unidade maior para bytes
    static public function toFormatSize($size, $unitOr, $unitDest, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = self::toBytes($size, $unitOr);
        $pow = array_search($unitDest, $units);

        return round($bytes / pow(1024, $pow), $precision);
    }

    //Para exibir tamanho dos arquivos no formato ideal
    static public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    //Executa Commands
    static public function runCommand($command, $args) {
        $fullArgs = array('yiic', $command);
        foreach ($args as $arg) {
            $fullArgs[] = $arg;
        }
        $commandPath = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'commands';
        $runner = new CConsoleCommandRunner();
        $runner->addCommands($commandPath);
        $runner->run($fullArgs);
    }

    static function getWebservice($params = array(), $limit = 10, $debug = false) {
        $params = array_merge(array('protocol' => 'http'), $params);
        $http_query = $params['query'];
        if (is_array($http_query))
            $http_query = http_build_query($http_query);
        $url = "{$params['protocol']}://{$params['server']}:{$params['port']}?{$http_query}";
        if ($debug) {
            echo "[" . date('Y-m-d H:i:s') . "] - ";
        }
        $json = @file_get_contents($url);

        if ($json) {
            return json_decode($json)->result;
        } else {
            if ($debug) {
                echo "Falha ao resgatar dados da url $url\n";
            }
            if ($limit > 0) //Permite tentar enviar até 3 vezes
                return self::getWebservice($params, $limit - 1, $debug);
            return false;
        }
    }

    static public function getDirSize($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        //Apenas para WINDOWS
        $obj = new COM('scripting.filesystemobject');
        if (is_object($obj)) {
            $ref = $obj->getfolder($dir);
            $obj = null;
            try {
                return $ref->size;
            } catch (Exception $e) {
                return false;
            }
        }

        //Para LINUX
        /*
          $io = popen ( '/usr/bin/du -sk ' . $dir, 'r' );
          $size = fgets ( $io, 4096);
          $size = substr ( $size, 0, strpos ( $size, "\t" ) );
          pclose ( $io );
          return $size;
         */
    }

    //Transforma objeto em array
    function obj2arr($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map(__FUNCTION__, $data);
        } else {
            return $data;
        }
    }

    static function mkdir($dir, $mode = 0777, $recursive = false) {
        if (!is_dir($dir)) {
            CFileHelper::createDirectory($dir, $mode, $recursive);
        }
    }

    //Usar em rotinas chamadas por commands
    static function getBaseUrl($full = false) {
        if (php_sapi_name() == "cli") {
            return 'http://' . $_SERVER['SERVER_NAME'];
        }
        return Yii::app()->getBaseUrl($full);
    }

    //Usar em rotinas chamadas por commands
    static function getWebroot() {
        $webroot = Yii::getPathOfAlias('webroot');
        if (php_sapi_name() == "cli") {
            $webroot = dirname($webroot);
        }
        return $webroot;
    }

    static function normalizeSeps($path, $out = DIRECTORY_SEPARATOR) {
        return str_replace(array('\\', '/'), $out, $path);
    }

    static function func_argNames($funcName) {
        if (is_array($funcName)) {
            $f = new ReflectionMethod($funcName[0], $funcName[1]);
        } else {
            $f = new ReflectionFunction($funcName);
        }
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;
        }
        return $result;
    }

}

?>
