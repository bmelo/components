<?php

namespace BMelo;

/**
 * Description of Webservice
 *
 * @author Dono
 */
class Webservice {

    public static function Model2Array($models) {
        if (!is_array($models))
            return self::Model2ArrayRec($models);

        //Varre todos os modelos gerando um array grande
        $dados = array();
        foreach ($models as $model) {
            $dados[] = self::Model2ArrayRec($model);
        }
        return $dados;
    }

    private static function relations2Array($model, $relation) {
        if (empty($model->$relation)) {
            return null;
        }
        if (is_array($model->$relation)) { //HAS MANY ou MANY TO MANY
            $data = array();
            foreach ($model->$relation as $related) {
                $data[] = self::Model2ArrayRec($related);
            }
        } else //BELONGS TO ou HAS ONE
            $data = self::Model2ArrayRec($model->$relation);
        return $data;
    }

    /**
     * @param CActiveRecord $model
     * */
    private static function Model2ArrayRec($model) {
        $data = $model->attributes;
        //Levantando relacionamentos
        $rels = $model->relations();
        foreach ($rels as $relation => $params) { //Irá gerar um vetor com todos os relacionamentos
            if ($model->hasRelated($relation)) {
                $data[$relation] = self::relations2Array($model, $relation);
            }
        }
        return $data;
    }

}
