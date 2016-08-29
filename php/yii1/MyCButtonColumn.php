<?php
/**
 * MyCButtonColumn class file.
 *
 * @author Bruno Melo <bruno.raphael@gmail.com>
 */
Yii::import('zii.widgets.grid.CButtonColumn');

class MyCButtonColumn extends CButtonColumn {
	
	public $name;
	public $value;
	public $sortable=true;
	public $type='text';
	public $filter = true;

	public $filterHtmlOptions = array();
	
	public function init()
	{
		parent::init();
		if($this->name===null)
			$this->sortable=false;
		if($this->name===null && $this->value===null)
			throw new CException(Yii::t('zii','Either "name" or "value" must be specified for CDataColumn.'));
	}

	public function renderFilterCell() {
		echo CHtml::openTag('td',$this->filterHtmlOptions);
		$this->renderFilterCellContent();
		echo "</td>";
	}

	protected function renderFilterCellContent() {
		if(is_string($this->filter))
			echo $this->filter;
		else if($this->filter!==false && $this->grid->filter!==null && $this->name!==null && strpos($this->name,'.')===false)
		{
			if(is_array($this->filter))
				echo CHtml::activeDropDownList($this->grid->filter, $this->name, $this->filter, array('id'=>false,'prompt'=>''));
			else if($this->filter===null)
				echo CHtml::activeTextField($this->grid->filter, $this->name, array('id'=>false));
		}
		else
			parent::renderFilterCellContent();
	}
  
  protected function evaluateMixed($var, $row, $data){
    if(!is_string($var)){
      return $var;
    }
    $eVal = @$this->evaluateExpression($var,array('row'=>$row,'data'=>$data));
    return $eVal ? $eVal : $var;
  }
  
  protected function resolveData($var,$row,$data){
    if(is_array($var)){
      $out = array();
      foreach( $var as $key=>$value ){
        $out[$key] = $this->resolveData($value, $row, $data);
      }
    }else{
      $out = $this->evaluateMixed($var, $row, $data);
    }
    return $out;
  }

	protected function renderButton($id,$button,$row,$data)
	{
		if (isset($button['visible']) && !$this->evaluateExpression($button['visible'],array('row'=>$row,'data'=>$data)))
  			return;
		if (isset($button['enable'])){
			$enable = $this->evaluateExpression($button['enable'],array('row'=>$row,'data'=>$data)) ? 'enabled' : 'disabled';
			if (!isset($button['options']['class']))
				$button['options']['class'] = $enable;
			else
				$button['options']['class'] .= ' '.$enable;
		}
		$label=isset($button['label']) ? $button['label'] : $id;
		$url=isset($button['url']) ? $this->evaluateExpression($button['url'],array('data'=>$data,'row'=>$row)) : '#';
		$options=isset($button['options']) ? $button['options'] : array();
    $options = $this->resolveData($options, $row, $data);
		if(!isset($options['title']))
			$options['title']=$label;
		if(isset($button['imageUrl']) && is_string($button['imageUrl']))
			echo CHtml::link(CHtml::image($button['imageUrl'],$label),$url,$options);
		else
			echo CHtml::link($label,$url,$options);
	}

}