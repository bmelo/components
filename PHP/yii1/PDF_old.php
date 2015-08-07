<?php
/**
 * Description of CPDF
 *
 * @author bruno.melo
 */
class CPDF {

    protected static function loadLib($lib) {
        require_once( Yii::getPathOfAlias('custom.vendors.pdf-generators') . $lib );
    }

    protected static function HTML2PDF($html, $fileout) {
        self::loadLib('/html2pdf/html2pdf.class.php');

        $html2pdf = new HTML2PDF('P', 'A4', 'en');
        $html2pdf->WriteHTML($html);
        return $html2pdf->Output($fileout, 'F');
    }

    protected static function MPDF($html, $fileout) {
        self::loadLib('/mpdf/mpdf.php');

        $html2pdf = new mPDF('c', 'A4'); //('P', 'A4', 'en');
        $html2pdf->WriteHTML($html);
        $html2pdf->Output($fileout, 'F');
    }

    public static function convertHTML($html, $fileout, $rewrite = true, $lib = 'MPDF') {
        if ($rewrite OR ! is_file($fileout))
            return self::$lib($html, $fileout);
    }

}
