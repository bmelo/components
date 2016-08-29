<?php

/**
 * @copyright Copyright &copy; Thiago Talma, thiagomt.com, 2014
 * @package yii2-fullcalendar
 * @version 1.0.0
 */

namespace bmelo\yii2\widgets;

use Yii;
use yii\bootstrap\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use bmelo\yii2\assets\FullCalendarAsset;
use bmelo\yii2\assets\PrintAsset;
use bmelo\yii2\assets\GoogleCalendarAsset;

/**
 * Talma FullCalendar widget is a Yii2 wrapper for the FullCalendar.
 *
 * @author Thiago Talma <thiago@thiagomt.com>
 * @since 1.0
 * @see http://arshaw.com/fullcalendar
 */
class FullCalendar extends Widget {

    /**
     * @var array Additional config
     */
    public $config = [];

    /**
     * @var string Text for loading alert
     */
    public $loading = 'Loading...';

    /**
     * @var boolean If the plugin displays a Google Calendar.
     */
    public $googleCalendar = false;

    /**
     * Runs the widget.
     */
    public function run() {
        $this->registerClientScript();

        Html::addCssClass($this->options, 'fullcalendar');

        echo '<div id="container_' . $this->options['id'] . '">';
        echo '<div class="fc-loading" style="display: none;">' . $this->loading . '</div>';
        echo Html::tag('div', '', $this->options);
        echo '</div>';
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript() {
        $view = $this->getView();

        $asset = FullCalendarAsset::register($view);
        PrintAsset::register($view);

        $language = isset($this->config['lang']) ? $this->config['lang'] : Yii::$app->language;
        $language = strtolower($language);
        if ($language != 'en-us') {
            $view->registerJsFile("{$asset->baseUrl}/lang/{$language}.js", [
                'depends' => ['bmelo\yii2\assets\FullCalendarAsset']
            ]);
        }
        if ($this->googleCalendar) {
            GoogleCalendarAsset::register($view);
        }

        $options = $this->getClientOptions();
        $options = Json::encode($options);
        $view->registerJs("jQuery('#{$this->options['id']}').fullCalendar({$options});");
    }

    /**
     * @return array the options for the text field
     */
    protected function getClientOptions() {
        $id = $this->options['id'];

        $options['loading'] = new JsExpression("function(isLoading, view ) {
                $('#container_{$id}').find('.fc-loading').toggle(isLoading);
        }");
        $options['viewRender'] = new JsExpression("function (view, element) {
            idAgenda = '#{$this->options['id']}';
            if (this.calendar.options.maxDate < view.end)
                $(idAgenda + ' .fc-next-button').addClass('fc-state-disabled');
            else
                $(idAgenda + ' .fc-next-button').removeClass('fc-state-disabled');
            if (view.start < this.calendar.options.minDate)
                $(idAgenda + ' .fc-prev-button').addClass('fc-state-disabled');
            else
                $(idAgenda + ' .fc-prev-button').removeClass('fc-state-disabled');
        }");

        $options = array_merge($options, $this->config);
        return $options;
    }

}
