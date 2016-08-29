<?php

/**
 * Description of HtmlParser
 *
 * @author Bruno
 */
class HtmlParser {
    //Armazena todas as imagens externas no site localmente, substituindo no html
    static function internalImgs($html, $outDir = null, $headers = array()) {
        $root = Helper::getWebroot(true); //Diretório raiz do site
        $basePath = dirname(Helper::normalizeSeps(Yii::app()->basePath, '/'));
        $baseUrlFull = Helper::getBaseUrl(true);
        if ($outDir == null) {
            $outDir = $root . DS . 'assets' . DS . 'external';
        }
        Helper::mkdir($outDir);
        $imgs = self::extrairImgs($html, true);
        CRequests::downloadFiles($imgs, $outDir, $headers, true);
        foreach ($imgs as $img) {
            $path = ($img[0] == '/') ? $root . $img : $img; //Gera um path ou usa o que foi lido
            if( strpos($path, $baseUrlFull) !== 0){
                $internalPath = CRequests::downloadFile($path, $outDir, $headers, true);
                $internalPath = Helper::normalizeSeps($internalPath, '/');
                $internalUrl = str_replace( $basePath, $baseUrlFull, $internalPath );
                $html = str_replace($path, $internalUrl, $html);
            }
        }
        return $html;
    }

    //Deixa as URLs com caminho parcial
    static function normalizeImgs($html) {
        $baseUrlFull = Helper::getWebroot(true);
        $baseUrl = '/' . basename($baseUrlFull);
        return str_replace($baseUrlFull, $baseUrl, $html); //Limpa urls
    }

    //Exporta o src de todas as imagens do site
    static function extrairImgs($html, $normalize = false) {
        if ($normalize) {
            $html = self::normalizeImgs($html);
        }
        $pattern = "/<img\s*.*?\s*src=[\'\"](?P<url>.+?)[\'\"]\s*.*?\s*>/";
        $imgs = [];
        if (preg_match_all($pattern, $html, $imgs)) {
            $imgs = array_unique($imgs['url']);
        }else{
            $imgs = [];
        }
        return array_values($imgs);
    }

}
