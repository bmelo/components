<?php

namespace bmelo\yii2\widgets;

use kartik\typeahead\Typeahead;

/**
 * Description of Typeahead
 *
 * @author Bruno
 */
class TypeaheadSelect extends Typeahead {

    /**
     * @return void Validate if configuration is valid
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateConfig()
    {
        foreach ($this->dataset as $datum) {
            if (isset($datum['local']) && isset($datum['prefetch']) && isset($datum['remote'])) {
                throw new InvalidConfigException("No data source found for the Typeahead. The 'dataset' array must have one of 'local', 'prefetch', or 'remote' settings enabled.");
            }
        }
    }

}
