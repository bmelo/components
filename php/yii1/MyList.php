<?php
/**
 * Description of MyGrid
 * @author Bruno Melo <bruno.melo@idor.org>
 */

Yii::import('zii.widgets.CListView');
Yii::import('custom.components.grid.*');

class MyList extends CListView {

  public $ajaxUpdate = false;
  public $template = "{export}\n{filters}\n{pager}\n{items}\n{pager}";
  public $filters = array('class'=>'GridFilters', 'sameLine' => true);
  public $filterCleanColumn = true;
  public $export = array('class'=>'GridExport');
  public $afterAjaxUpdate = 'js:function(){init();}';
  protected $contents = array();

  protected function determinePageSize() {
    if (!isset(Yii::app()->session['pageSize']))
      Yii::app()->session['pageSize'] = $this->dataProvider->pagination->pageSize;
    if (isset($_GET['pageSize']))
      Yii::app()->session['pageSize'] = $_GET['pageSize'];
    return Yii::app()->session['pageSize'];
  }

  public function init() {
    $pageSize = $this->determinePageSize();
    if (!isset($this->htmlOptions['class']))
      $this->htmlOptions['class'] = 'list-view myGrid';
    $this->pager = array(
        'class' => 'MyPager',
        'headerGridID' => $this->id,
        'pageSize' => $pageSize,
    );
    $this->dataProvider->pagination->pageSize = $pageSize;
    return parent::init();
  }

  public function renderPager() {
    parent::renderPager();
    MyYii::registerCss('/css/mygrid.css');
  }
  
  private function renderGridComponent( $arr, $class ){
    $arr['gridId'] = $this->id;
    CGridExtra::runWidget($arr, $class);
  }
  
  public function renderFilters(){
    $this->filters['numItems'] = $this->dataProvider->getPagination()->itemCount;
    $this->renderGridComponent($this->filters, 'GridFilters');
  }
  
  public function renderExport(){
    $this->renderGridComponent($this->export, 'GridExport');
  }

  protected function addCleanFiltersColumn() {
    $columnFilter = new CDataColumn($this);
    $columnFilter->value = '';
    $columnFilter->filterHtmlOptions = array('class' => 'cleanFilter');
    $columnFilter->headerHtmlOptions = array('class' => 'clean');
    $columnFilter->htmlOptions = array('class' => 'clean');
    $urlClean = Yii::app()->baseUrl . '/' . Yii::app()->controller->route;
    $columnFilter->filter = "<a href='{$urlClean}' class='delfilter'></a>";
    $this->columns[] = $columnFilter;
  }

  protected function initColumns() {
    parent::initColumns();
    if( $this->filter !== null and $this->filterCleanColumn ){ //Adiciona coluna para limpar filtros
      $this->htmlOptions['class'].=' hasFilter';
      $this->addCleanFiltersColumn();
    }
  }

}
