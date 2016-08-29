<?php

/**
 * Description of MyGrid
 * @author Bruno Melo <bruno.melo@idor.org>
 */
Yii::import('zii.widgets.grid.CGridView');
Yii::import('custom.components.grid.*');

class MyGrid extends CGridView {

  public $ajaxUpdate = false;
  public $template = "{export}\n{filters}\n{pager}\n{items}\n{pager}";
  public $filters = array('class' => 'GridFilters', 'sameLine' => true);
  public $filterCleanColumn = true;
  public $ignoreRenderKeys = false;
  public $afterAjaxUpdate = 'js:function(){init();}';
  public $export = array('class' => 'GridExport');
  protected $contents = array();

  protected function determinePageSize() {
    $size = $this->dataProvider->pagination->pageSize;
    if (isset($_GET['pageSize'])){
      $size = $_GET['pageSize'];
    }
    Yii::app()->session['pageSize'] = $size;
    return $size;
  }

  public function init() {
    $pageSize = $this->determinePageSize();
    if (!isset($this->htmlOptions['class']))
      $this->htmlOptions['class'] = 'grid-view myGrid';
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
  
  public function renderKeys() {
      if( !$this->ignoreRenderKeys ){
        parent::renderKeys();
      }
  }

  private function renderGridComponent($arr, $class) {
    $arr['gridId'] = $this->id;
    CGridExtra::runWidget($arr, $class);
  }

  public function renderFilters() {
    $this->filters['numItems'] = $this->dataProvider->getPagination()->itemCount;
    $this->renderGridComponent($this->filters, 'GridFilters');
  }

  public function renderExport() {
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
    if ($this->filter !== null and $this->filterCleanColumn) { //Adiciona coluna para limpar filtros
      $this->htmlOptions['class'].=' hasFilter';
      $this->addCleanFiltersColumn();
    }
  }

  protected function createDataColumn($text) {
    if (!preg_match('/^([\w\.\ à-úÀ-Ú]+)(:(\w*))?(:(.*))?$/', $text, $matches))
      throw new CException(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
    $column = new CDataColumn($this);
    $column->name = $matches[1];
    if (isset($matches[3]) && $matches[3] !== '')
      $column->type = $matches[3];
    if (isset($matches[5]))
      $column->header = $matches[5];
    return $column;
  }

}
