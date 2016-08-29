<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace bmelo\yii2\widgets;

/**
 *
 * @author Bruno
 */
interface TimelineEvent {
    /** You can extend this object with any other property, getters and setters **/

    public function getTime();

    public function getHeader();

    public function getBody();

    public function getFooter();

    public function getIconClass();

    public function getIconBg();
}
