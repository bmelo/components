<?php

namespace bmelo\yii2\filters;

use bmelo\yii2\helpers\Url;

/**
 * Description of RememberUrlFilter
 *
 * @author Bruno
 */
class RememberUrl extends \yii\base\ActionFilter{
    
    /**
     * @inheritdoc
     */
    public function afterAction($action, $result) {
        $parentsResults = parent::afterAction($action, $result);
        Url::rememberStack();
        return $parentsResults;
    }
}
