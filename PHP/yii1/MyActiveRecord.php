<?php
class MyActiveRecord extends CActiveRecord {

  const TEXT = 0;
  const TEXTAREA = 1;
  const CHECKBOX = 2;
  const RADIO = 3;
  const BOOL = 4;
  const DATE = 5;
  const BINARY = 6;
  
  public $listId = 'id';
  public $listLabel = null;

  private $_fieldTypes = array();
  static $types = array(
      'int' => self::TEXT,
      'smallint' => self::TEXT,
      'varchar' => self::TEXT,
      'datetime' => self::DATE,
      'bit' => self::BINARY,
      'tinyint' => self::BINARY,
  );

  public function behaviors() {
    return array(
        'CorrecoesPtBr' => array('class' => 'custom.extensions.behaviors.CorrecoesPtBr')
    );
  }

  //Retorna o tipo utilizado no formulário
  public function getFieldTypes() {
    if (empty($this->_fieldTypes)) {
      foreach ($this->getTableSchema()->columns as $columnName => $column) {
        $this->_fieldTypes[$columnName] = self::$types[$column->dbType];
      }
    }
    return $this->_fieldTypes;
  }

  //Altera o tipo utilizado no formulário
  public function setFieldTypes($field, $type) {
    if (empty($this->_fieldTypes)) {
      $this->getFieldTypes();
    }
    if (empty($type)) {
      unset($this->_fieldTypes[$field]);
    } else {
      $this->_fieldTypes[$field] = $type;
    }
  }

  public function afterSave() {
    $pk = (empty($this->primaryKey) or is_array($this->primaryKey)) ? 'id' : $this->primaryKey;
    if (isset($this->$pk) and empty($this->$pk)) {
      $this->$pk = Yii::app()->db->lastInsertID;
    }
    return parent::afterSave();
  }
  
  /**
   * Cria modelo usando atributos passados na função
   * @param string[] $attributes
   * @param string $scope
   */
  public static function create( $attributes, $scope = 'search' ){
      $model = new static($scope);
      $model->attributes = $attributes;
      return $model;
  }
  
  //RETORNA TODOS OS MODELOS - ARMAZENA EM CACHE
  //Funções auxiliares para a montagem das listagens e o uso de cache
  protected function dbCriteriaToStr(){
    $params = $this->dbCriteria->toArray();
    $out = '';
    foreach( $params as $key=>$val ){
      if( empty($val) ) continue;
      if( is_array($val) )
        $val = implode(',', $val);
      elseif( !is_string( $val ) )
        $val = strval($val);
      $out.="_{$key}[{$val}]";
    }
    return $out;
  }
  
  //Retorna a próxima chave primaria - Só funciona com chave primária númerica com autoincrement
  public static function nextPK() {
    $model = static::model();
    $criteria=new CDbCriteria;
    $pk = empty($model->primaryKey()) ? $model->tableSchema->primaryKey : $model->primaryKey();
    $criteria->select = "max(t.{$pk})+1 AS $pk";
    $model->find($criteria);
    return $model->find($criteria)->$pk;
  }
  
  protected function getIDCache(){
    $id = get_class($this) . '_list';
    return $id.$this->dbCriteriaToStr();
  }
  
  //Lógica Principal
  public function setListParams( $id, $name = null ){
      $this->listId = $id;
      if( $name !== null && is_string($name)){
          $this->listLabel = $name;
      }
      return $this;
  }
  
  public function getList($condition = '', $params = array(), $reload = false) {
    $idCache = $this->getIDCache();
    $models = Yii::app()->cache->get( $idCache );
    if ($models === false or $reload) {
      $this->dbCriteria->condition = $condition;
      $this->dbCriteria->params = $params;
      $models = $this->findAll();
      Yii::app()->cache->set($idCache, $models, 3600);
    }
    return $models;
  }

  public function getListArray($condition = '', $params = array(), $reload = false) {
    if( empty( $this->listLabel ) )
      throw new CException( "Attributo listLabel não definido" );
    $models = $this->getList($condition, $params, $reload);
    return CHtml::listData($models, $this->listId, $this->listLabel);
  }
  
  //Adiciona relacionamentos diretamente (@todo)
  public function addHasManyDirect($relId, $modelMM, $ids){
      $rels = $this->relations();
      preg_match('/\w+\(\s*([\w\_\-]+)\s*,\s*([\w\_\-]+)\)/','EditalVerba(edital_id, verba_id)', $matches);
      $status = false;
      foreach($ids as $id){
          $model = new $modelMM;
          $model->{$matches[1]} = $this->id;
          $model->{$matches[2]} = $id;
          $status = ($model->save() and true);
      }
      return $status;
  }
}

?>
