<?php

defined('TYPO3_MODE') || die();

(function () {
    if (TYPO3_MODE === 'BE') {
        // Register TCA validator
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'] =
            ['PrototypeIntegration\PrototypeIntegration\Evaluator\PhoneNumberValidation' => ''];
    }

    $GLOBALS['TYPO3_CONF_VARS']['FE']['ContentObjects']['PTI'] =
        \PrototypeIntegration\PrototypeIntegration\ContentObject\PtiContentObject::class
    ;

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['view']['viewResolver'] = \PrototypeIntegration\PrototypeIntegration\View\DefaultViewResolver::class;
})();
