<?php

namespace bmelo\yii2\widgets;

use Yii;
use yii\bootstrap\Html;
use dosamigos\multiselect\MultiSelect;

/**
 * Description of DataColumn
 *
 * @author bruno.melo
 */
class DataColumn extends \yii\grid\DataColumn {

    /**
     * Returns array with two elements, first the type and second the options
     * 
     * @return array
     */
    protected function columnType() {
        $model = $this->grid->filterModel;
        //Try specified type
        if (method_exists($model, 'attributeFilters')) {
            $filters = $model->attributeFilters();
            if (isset($filters[$this->attribute])) {
                return $filters[$this->attribute];
            }
        }
        //Try dbType
        $column = $model->getTableSchema()->getColumn($this->attribute);
        if ($column !== null) {
            return [$column->dbType, []];
        }
        //default
        return null;
    }

    /**
     * @param array $dataFilter
     * @return string
     */
    protected function filterMultiselect( $dataFilter ) {
        \bmelo\yii2\assets\MultiselectFilterAsset::register( Yii::$app->getView() );
        $opts = \yii\helpers\ArrayHelper::getValue($dataFilter, 2, []);
        //Defaults
        $options = [
            "options" => ['multiple' => "multiple"],
            'data' => $dataFilter[1],
            'model' => $this->grid->filterModel,
            'attribute' => $this->attribute,
            "clientOptions" => array_merge([
                'allSelectedText' => 'Selecionar todos',
                'includeSelectAllOption' => true,
                'numberDisplayed' => 1,
                'selectAllText' => 'Todos',
                'nSelectedText' => 'selecionados',
                'nonSelectedText' => "<span class='empty'></span>",
                'buttonTitle' => new \yii\web\JsExpression('function(){return null;}'),
                //'buttonWidth' => 'calc(100% - 38px)',
                //'enableFiltering' => true
            ], $opts),
        ];
        return MultiSelect::widget( $options );
    }
    
    /**
     * @param array $dataFilter
     * @return string
     */
    protected function filterDate( $dataFilter ) {
        return null;
    }
    
    /**
     * @param array $dataFilter
     * @return string
     */
    protected function filterDateTime( $dataFilter ) {
        return null;
    }

    protected function getFilterInputByType() {
        $model = $this->grid->filterModel;
        if ($this->filter !== false && $this->attribute !== null && $model->isAttributeActive($this->attribute)) {
            $filterOptions = $this->columnType();
            switch ($filterOptions[0]) {
                case 'date':
                    return $this->filterDate($filterOptions);
                case 'smalldatetime': case 'datetime':
                    return $this->filterDateTime($filterOptions);
                case 'multiselect':
                    return $this->filterMultiselect($filterOptions);
            }
        }
        return null;
    }

    protected function getFilterError() {
        $model = $this->grid->filterModel;
        if ($model->hasErrors($this->attribute)) {
            Html::addCssClass($this->filterOptions, 'has-error');
            return ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderHeaderCellContent() {
        return parent::renderHeaderCellContent();
    }

    /**
     * @inheritdoc
     */
    protected function renderFilterCellContent() {
        if (is_string($this->filter)) {
            return $this->filter;
        }
        //generates input following dbType
        $input = $this->getFilterInputByType();
        if ($input !== null) {
            return $input . $this->getFilterError();
        }

        //default
        return parent::renderFilterCellContent();
    }

}
