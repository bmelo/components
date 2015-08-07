<?php

/**
 * Description of MyPager
 * @author Bruno Melo <bruno.melo@idor.org>
 */
class MyPager extends CLinkPager {

    public $pageSize = 50;
    public $sizes = array(20 => 20, 50 => 50, 100 => 100, 500 => 500, 1000 => 1000);
    public $prevPageLabel = "<i class='fa fa-chevron-left no-shadow'></i>";
    public $nextPageLabel = "<i class='fa fa-chevron-right no-shadow'></i>";
    public $headerGridID = null;

    public function init() {
        if (!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = 'yiiPager myPager';
        if( !in_array($this->pageSize, $this->sizes) ){
            $this->sizes[$this->pageSize] = $this->pageSize;
            sort($this->sizes); //Ordena os tamanhos
            $this->sizes = array_combine($this->sizes, $this->sizes);
        }
        $this->header = '';
        return parent::init();
    }

    public function run() {
        $this->registerClientScript();
        $buttons = $this->createPageButtons();
        $content = $this->header;
        $content.= CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));
        $content.= $this->footer;
        if ($this->getPageCount() <= 1)
            $content = "<div class='pager'>{$content}</div>";
        echo $content;
    }

    public function getListSizes() {
        if (empty($this->headerGridID))
            return '';
        $optionsSize = CHtml::dropDownList('pageSize', $this->pageSize, $this->sizes, array(
              'onchange' => "$.tools.updateGrid('{$this->headerGridID}', {pageSize:$(this).val()} );",
        ));
        return "<span class='pageSize'>Exibir linhas: {$optionsSize}</span>";
    }

    protected function getListPages() {
        $pageCount = $this->getPageCount();
        if ($pageCount <= 1)
            return '';
        $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
        $pageVar = $this->getPages()->pageVar;
        $pagesNum = array();
        for ($i = 1; $i <= $pageCount; $i++) {
            $pagesNum[$i] = $i;
        }
        $pages = CHtml::dropDownList($pageVar, $currentPage + 1, $pagesNum, array(
              'onchange' => "$.tools.updateGrid('{$this->headerGridID}', {'{$pageVar}':$(this).val()} );",
        ));
        return "<span class='pages'>Ir para: {$pages}</span>";
    }

    protected function getSummary() {
        $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
        $firstRow = (($currentPage) * $this->pageSize) + 1;
        $lastRow = ($currentPage + 1) * $this->pageSize;
        if ($lastRow > $this->itemCount) {
            $firstRow = max(array($this->itemCount - $this->pageSize + 1, 1));
            $lastRow = $this->itemCount;
        }
        return "{$firstRow} - {$lastRow} de {$this->itemCount}";
    }

    protected function createPageButtonNav($label, $page, $disabled) {
        $htmlOptions['class'] = $disabled ? 'disabled' : '';
        $url = $disabled ? null : $this->createPageUrl($page);
        $buttonType = 'link';
        return compact('label', 'url', 'buttonType', 'htmlOptions');
    }

    protected function getNavigationButtons($buttons) {
        return $this->widget(
                'booster.widgets.TbButtonGroup', [
                  'encodeLabel' => false, 'buttons' => $buttons,
                  'size'=>'small'
                  ]
                , true);
    }

    /**
     * Creates the page buttons.
     * @return array a list of page buttons (in HTML code).
     */
    protected function createPageButtons() {
        if ($this->itemCount <= 0)
            return array();
        list($beginPage, $endPage) = $this->getPageRange();
        $currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()

        $buttons = array();
        $buttons[] = '<span class="pageSize">' . $this->getListSizes() . "</span>";
        $buttons[] = '<span class="pagesNum">' . $this->getListPages() . "</span>";
        $buttons[] = '<span class="pagesSummary">' . $this->getSummary() . "</span>";

        $btPrev = $this->createPageButtonNav($this->prevPageLabel, $currentPage - 1, $currentPage <= 0);
        $btNext = $this->createPageButtonNav($this->nextPageLabel, $currentPage + 1, $currentPage >= $endPage);
        $buttons[] = $this->getNavigationButtons(array($btPrev, $btNext));

        return $buttons;
    }

}
