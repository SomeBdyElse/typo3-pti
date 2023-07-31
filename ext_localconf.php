<?php

defined('TYPO3') or die();

(function () {
    // Register TCA validator
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'] = ['PrototypeIntegration\PrototypeIntegration\Evaluator\PhoneNumberValidation' => ''];
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['view']['viewResolver'] = \PrototypeIntegration\PrototypeIntegration\View\DefaultViewResolver::class;
})();
