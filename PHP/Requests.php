<?php

/**
 * Description of CRequests
 *
 * @author Bruno
 */
class CRequests {

    static function getCurlDownload($url, $headers = array()) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_HEADER => 0,
          CURLOPT_TIMEOUT => 5
        ]);
        return $ch;
    }

    protected static function geraFilename($url, $outDir = null, $hash = false) {
        if ($outDir === null) {
            $outDir = Yii::app()->runtimePath;
        }
        $name = $hash ? md5($url) : tempnam('', 'external');
        $ext = CFileHelper::getExtension(basename($url));
        return "$outDir/$name.$ext";
    }

    protected static function limpaUrlsBaixadas($urls, $rewrite = false, $outDir = null, $hash = false) {
        $total = count($urls);
        for($n=-1;  $total > ++$n;){
            if(!is_string($urls[$n])){ unset($urls[$n]); }
            $fname = self::geraFilename($urls[$n], $outDir, $hash);
            if (!($rewrite || !is_file($fname) || filesize($fname) == 0)) {
                unset($urls[$n]);
            }
        }
        return array_values($urls);
    }

    static function downloadFiles($urls, $outDir = null, $headers = array(), $hash = false, $rewrite = false, $maxRetry = 2) {
        $chs = $fails = [];
        $urls = self::limpaUrlsBaixadas($urls, $rewrite, $outDir, $hash);
        foreach ($urls as $url) {
            $chs[] = self::getCurlDownload($url, $headers);
        }
        $dados = self::multipleRequests($chs);
        //Salva arquivos e gera lista de falhas
        for ($n = -1; count($urls) > ++$n;) {
            if (empty(trim($dados[$n]))) {
                $fails[] = $urls[$n];
                continue;
            }
            $filename = self::geraFilename($urls[$n], $outDir, $hash);
            file_put_contents($filename, $dados[$n]);
        }

        if (count($fails) > 0 && $maxRetry-- > 0) {
            self::downloadFiles($fails, $outDir, $headers, $hash, $rewrite, $maxRetry);
        }
    }

    static function downloadFile($url, $outDir = null, $headers = array(), $hash = false, $rewrite = false) {
        $filename = self::geraFilename($url, $outDir, $hash);
        //FAZ A REQUISIÇÃO QUANDO NECESSÁRIO
        if ($rewrite || !is_file($filename) || filesize($filename) == 0) {
            $fp = fopen($filename, 'wb');
            $ch = self::getCurlDownload($url, $headers);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        return $filename;
    }

    static function multipleRequests(&$chs) {
        //create the multiple cURL handle
        $mh = curl_multi_init();
//        curl_multi_setopt($mh, CURLMOPT_PIPELINING, 1);
        // Loop over pages and get set the URL to the cURL queue
        foreach ($chs as $ch) {
            if (gettype($ch) == 'resource' && get_resource_type($ch) == 'curl') {
                curl_multi_add_handle($mh, $ch);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // store the page contents
            }
        }

        // Execute all queries simultaneously, and continue until all are complete
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        //Getting outputs        
        $outs = [];
        foreach ($chs as $ch) {
            $outs[] = curl_multi_getcontent($ch);
        }
        return $outs;
    }

}
