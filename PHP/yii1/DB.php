<?php

/**
 * Description of DB
 *
 * @author Bruno
 */
class DB {

    /**
     * @param mixed $obj
     * @param array $params
     * @return array
     */
    protected static function getParams($obj, $params) {
        $table = is_array($obj) ? $obj[0] : $obj->tableSchema;
        //Preparando commando
        $criteria = is_array($obj) ? $obj[1] : $obj->dbCriteria;
        $criteria->select = $params;
        $criteria->group = implode(',', $params);
        $criteria->distinct = true;
        $criteria->order = end($params) . ' ASC';
        $criteria->addCondition($params[0] . ' IS NOT NULL');
        return [$table, $criteria];
    }

    /** @param mixed $model Can be CActiveRecord or array[string, CDbCriteria] */
    public static function filters($data, $id, $value = null, $cache = true) {
        if ($value === null) {
            $value = $id;
        }
        $params = array_unique(array($id, $value));

        list($table, $criteria) = self::getParams($data, $params);
        $command = Yii::app()->db->commandBuilder->createFindCommand($table, $criteria);
        //Resgatando os dados
        $idCache = 'DB::filters_' . md5($command->text . '_' . implode('_', $criteria->params));
        $models = Yii::app()->cache->get($idCache);
        if ($models === false or ! $cache) {
            $models = $command->queryAll();
            Yii::app()->cache->set($idCache, $models, 500);
        }
        return CHtml::listData($models, $id, $value);
    }

    public static function execStoredProcedure($procedure, $params) {
        //Lógica aparenta estar OK, mas algum detalhe está atrapalhando alguns comandos
        $sql = "EXECUTE $procedure ";
        $paramsBind = [];
        $i = 0;
        foreach( $params as $param=>$val ){
            $p = ":paramSP".(++$i);
            $sql .= "@$param=$p, ";
            $paramsBind[$p] = $val;
        }
        $command = Yii::app()->db->createCommand( trim($sql,", ") );
        foreach( $paramsBind as $param=>$val){
            $command->bindParam($param, $val);
        }
        return $command->execute( $paramsBind );
    }

}
