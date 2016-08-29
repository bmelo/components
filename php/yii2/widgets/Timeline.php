<?php

namespace bmelo\yii2\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\base\Widget;

/**
 * Widget for Timelines
 *
 * @author bruno.melo
 */
class Timeline extends Widget {

    const TYPE_NAVY = 'navy';
    const TYPE_LBLUE = 'light-blue';
    const TYPE_BLUE = 'blue';
    const TYPE_AQUA = 'aqua';
    const TYPE_RED = 'red';
    const TYPE_GREEN = 'green';
    const TYPE_YEL = 'yellow';
    const TYPE_PURPLE = 'purple';
    const TYPE_MAR = 'maroon';
    const TYPE_TEAL = 'teal';
    const TYPE_OLIVE = 'olive';
    const TYPE_LIME = 'lime';
    const TYPE_ORANGE = 'orange';
    const TYPE_FUS = 'fuchsia';
    const TYPE_BLACK = 'black';
    const TYPE_GRAY = 'gray';

    /*     * @var [] $items array of events
     *
     * @example
     *  'items'=>[
     *     '07.10.2014'=>[array of TimelineItems ],
     *     'some object'=>[array of TimelineItems ],
     *     '15.11.2014'=>[array of TimelineItems ],
     *     'some object'=>[array of TimelineItems ],
     *  ]
     *
     * */

    public $items = [];

    /**
     * string|\Closure that return string
     *
     * @example
     * 'defaultDateBg'=>function($data){
     *      if(is_string($data)){
     *          return insolita\wgadminlte\Timeline::TYPE_BLUE;
     *      }elseif($data->type==1){
     *          return insolita\wgadminlte\Timeline::TYPE_LBLUE;
     *      }else{
     *         return insolita\wgadminlte\Timeline::TYPE_TEAL;
     *      }
     * }
     * */
    public $defaultDateBg = self::TYPE_GREEN;

    /** callable function(obj) for prepare data
     *
     * @example
     * 'dateFunc'=>function($data){
     *     return date('d/m/Y', $data)
     * }
     *
     * @example
     * 'dateFunc'=>function($data){
     *     return is_object($data)?date('d/m/Y', $data->created):$data;
     * }
     *
     * */
    
    public $options = null;
    public $dateFunc = null;
    public $groupKeyFunc = null;
    protected $groups = null;

    public function init() {
        //Defining default function
        if ($this->groupKeyFunc === null) {
            $this->groupKeyFunc = function($data) {
                return call_user_func($this->dateFunc, $data);
            };
        }
        //Putting timeline class
        $this->options['class'] = isset( $this->options['class'] ) ? 'timeline '.$this->options['class'] : 'timeline';
        return parent::init();
    }

    public function run() {
        echo Html::tag('ul', $this->renderItems(), $this->options);
    }

    protected function renderItems() {
        $res = '';
        if (empty($this->items)) {
            return null;
        }
        $this->groupItems();
        foreach ($this->groups as $date => $events) {
            $res .= $this->renderGroup($date);
            foreach ($events as $event) {
                $res .= $this->renderEvent($event);
            }
        }
        $res .= $this->renderFooter();
        return $res;
    }

    protected function groupItems() {
        foreach ($this->items as $item) {
            $key = call_user_func($this->groupKeyFunc, $item);
            if (isset($this->groups[$key])) {
                $this->groups[$key][] = $item;
            } else {
                $this->groups[$key] = [$item];
            }
        }
    }

    protected function renderGroup($date) {
        $res = '';
        if (is_string($this->defaultDateBg)) {
            $res = Html::tag('span', $date, ['class' => 'bg-' . $this->defaultDateBg]);
        } elseif (is_callable($this->defaultDateBg)) {
            $class = call_user_func($this->defaultDateBg, $date);
            $res = Html::tag('span', $date, ['class' => 'bg-' . $class]);
        }
        return Html::tag('li', $res, ['class' => 'time-label']);
    }

    protected function renderEvent($ev) {
        $res = '';
        if ($ev instanceof TimelineEvent) {
            $data = [
                'time' => $ev->getTime(),
                'iconClass' => $ev->getIconClass(),
                'iconBg' => $ev->getIconBg(),
                'header' => $ev->getHeader(),
                'body' => $ev->getBody(),
                'footer' => $ev->getFooter()
            ];
            $res .= '<i class="fa fa-' . $data['iconClass'] . ' bg-' . $data['iconBg'] . '"></i>';
            $item = '';
            if ($data['time']) {
                $item .= Html::tag(
                    'span', Html::tag('i', '', ['class' => 'fa fa-clock-o']) . ' ' . $data['time'], ['class' => 'time']
                );
            }
            if ($data['header']) {
                $item .= Html::tag(
                    'h3', $data['header'], ['class' => 'timeline-header ' . (!$data['body'] && !$data['footer'] ? 'no-border' : '')]
                );
            }
            $item .= Html::tag('div', $data['body'], ['class' => 'timeline-body']);
            if ($data['footer']) {
                $item .= Html::tag('div', $data['footer'], ['class' => 'timeline-footer']);
            }
            $res .= Html::tag('div', $item, ['class' => 'timeline-item']);
        } else {
            throw new InvalidConfigException('event must be instanceof TimelineEvent');
        }

        return Html::tag('li', $res);
    }
    
    protected function renderFooter(){
        $res = Html::tag('i', '',['class' => 'fa fa-clock-o bg-' . self::TYPE_GRAY]);
        return Html::tag('li', $res, ['class' => 'time-label']);
    }

}
