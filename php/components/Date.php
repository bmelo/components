<?php

namespace bmelo\components;

class Date {

    public static function feriados($ano = null) {
        $ano = empty($ano) ? (int) date('Y') : $ano;
        $datas = [];
        $datas['pascoa'] = easter_date($ano);
        $datas['sexta_santa'] = strtotime("-2 day", $datas['pascoa']);
        $datas['carnaval'] = strtotime("-47 day", $datas['pascoa']);
        $datas['corpus_cristi'] = strtotime("+60 day", $datas['pascoa']);
        $feriados = [
            'Ano Novo' => '01/01/' . $ano,
            'Carnaval' => date('d/m/Y', $datas['carnaval']),
            'Sexta-Feira Santa' => date('d/m/Y', $datas['sexta_santa']),
            'P�scoa' => date('d/m/Y', $datas['pascoa']),
            'Tiradentes' => '21/04/' . $ano,
            'Dia do Trabalhador' => '01/05/' . $ano,
            'Corpus Cristi' => date('d/m/Y', $datas['corpus_cristi']),
            'Dia da Independ�ncia' => '07/09/' . $ano,
            'Nossa Senhora de Aparecida' => '12/10/' . $ano,
            'Dia de Finados' => '02/11/' . $ano,
            'Proclama��o da Rep�blica' => '15/11/' . $ano,
            'Natal' => '25/12/' . $ano
        ];
        return $feriados;
    }

    private static function numFeriadosPeriodo($startDay, $endDay) {
        $cont = 0;
        return $cont;
    }

    public static function idade($birth, $date = null) {
        if( $date === null ){
            $date = date('Y-m-d');
        }
        return static::diff($birth, $date)->y;
    }

    public static function isDayWeek($date) {
        return (date('N', $date) < 6);
    }

    public static function somaDias($numDias, $date = null) {
        $date = empty($date) ? time() : $date;
        return date("d/m/Y H:i:s", strtotime("+{$numDias} day", $date));
    }

    public static function maxNumDias($numDias, $ignorar = array()) {
        $ignorar = empty($ignorar) ? self::feriados() : $ignorar;
        $numSabDom = (($numDias % 7 + 1) * 2); //Sábados e domingos
        $numFeriados = ($numDias % 365 + 1) * count($ignorar); //Feriados
        return $numDias + $numSabDom + $numFeriados; //Número máximo para varredura
    }

    public static function somaDiasUteis($numDias, $date = null, $ignorar = array()) {
        $date = empty($date) ? time() : $date;
        $maxTests = self::maxNumDias($numDias, $ignorar); //Número máximo para varredura
        $cont = 0;
        while ($numDias > 0 and $cont < $maxTests) { //Percorre todos os dias checando se são dias úteis ou não
            $day = strtotime("+" . ++$cont . " day", $date);
            $dayStr = date("d/m/Y", $day);
            if (!in_array($dayStr, $ignorar) and self::isDayWeek($day)) { //Verifica se é um dia útil
                $numDias--;
            }
        }
        return date("d/m/Y H:i:s", strtotime("+{$cont} day", $date));
    }

    public static function somaDiasUteisFeriados($numDias, $date = null) {
        $date = empty($date) ? time() : $date;
        $feriados = array();
        //Resgata todos os feriados possíveis
        $startYear = (int) date('Y', $date);
        $maxNumDays = self::maxNumDias($numDias);
        $endYear = (int) date('Y', strtotime("+{$maxNumDays} day", $date));
        for (; $startYear <= $endYear; $startYear++) {
            foreach (self::feriados($startYear) as $feriado) {
                $feriados[] = $feriado;
            }
        }
        return self::somaDiasUteis($numDias, $date, $feriados);
    }

    static function formatDbDate($data) {
        if (is_string($data) && !preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
            $data = str_replace('-', '/', trim($data));
            $dia = explode('/', substr($data, 0, 10));
            return implode('-', array_reverse($dia)) . substr($data, 10);
        }
        return $data;
    }

    static function dataFormat($data, $format = 'd/m/Y') {
        if (empty($data)) {
            return null;
        }
        $data = self::formatDbDate(trim($data));
        if (strlen($data) == 10)
            $data.=' 00:00:00';
        return date($format, strtotime($data));
    }

    //Retorna diferença em segundos
    public static function diffDates($dateEnd, $dateStart) {
        if (is_string($dateStart)) {
            $dateStart = strtotime(self::formatDbDate($dateStart));
        }
        if (is_string($dateEnd)) {
            $dateEnd = strtotime(self::formatDbDate($dateEnd));
        }
        return $dateEnd - $dateStart;
    }

    static function toTimestamp($data) {
        if (preg_match('/\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}/', $data))
            $data.=':00';
        $data = self::formatDbDate($data);
        return strtotime($data);
    }

    static function getTimestamp($data) {
        if (strpos($data, '/'))
            $format = 'dd/MM/yyyy hh:mm:ss';
        else
            $format = 'yyyy-MM-dd hh:mm:ss';
        $size = min([strlen($data), strlen($format)]);
        $cropData = substr($data, 0, $size);
        $cropFormat = substr($format, 0, $size);
        return CDateTimeParser::parse($cropData, $cropFormat);
    }

    static function dataBr($date, $short = true) {
        $format = $short ? 'd/m/Y H:i' : 'd/m/Y H:i:s';
        return date($format, self::getTimestamp($date));
    }

    static function onlyDataBr($date) {
        return date('d/m/Y', self::getTimestamp($date));
    }

    /**
     * @param string $date1
     * @param string $date2
     * @return \DateInterval 
     */
    static function diff($date1, $date2) {
        $dataBegin = new \DateTime(static::formatDbDate($date1));
        $dataEnd = new \DateTime(static::formatDbDate($date2));
        return $dataBegin->diff($dataEnd);
    }

    static function diffTimestamp($date1, $date2) {
        $timeInterval = static::diff($date1, $date2);
        return (new \DateTime())->setTimeStamp(0)->add($timeInterval)->getTimeStamp();
    }

    static function formatDate($date, $formatOr = 'd/m/Y H:i:s', $formatDest = 'Y-m-d H:i:s') {
        if (empty($date)) {
            return null;
        }
        if ($formatOr === 'd/m/Y H:i:s' && strlen($date) > 19) {
            $date = substr($date, 0, 19);
        }
        $objDate = \DateTime::createFromFormat($formatOr, $date);
        if ($objDate) {
            return $objDate->format($formatDest);
        }
        return $date;
    }

    static function formatTime($time) {
        $time = preg_replace('/:\./', '', $time);
        $arTime = str_split(substr($time, 0, 6), 2);
        return implode(':', $arTime);
    }

    static function datemili($time = null, $precision = 3) {
        if ($time === null)
            $time = microtime(true);
        if ($precision < 1)
            $precision = 1;
        $now = DateTime::createFromFormat('U.u', $time);
        $datetime = $now->format("Y-m-d H:i:s.u");
        return substr($datetime, 0, 20 + $precision);
    }
    
    static function formatIntl( $date ){
        $fmt = new IntlDateFormatter(
                "pt_BR", 
                IntlDateFormatter::FULL, 
                IntlDateFormatter::FULL, 
                'America/Sao_Paulo',
                IntlDateFormatter::GREGORIAN, 
                "d 'de' MMMM 'de' y"
            );
        return $fmt->parse($date);
    }

}
