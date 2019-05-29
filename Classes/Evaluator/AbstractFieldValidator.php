<?php
namespace PrototypeIntegration\PrototypeIntegration\Evaluator;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract class for form validators
 */
abstract class AbstractFieldValidator
{
    /**
     * JavaScript validation
     *
     * @return string javascript function code for js validation
     */
    public function returnFieldJs()
    {
        return 'return value;';
    }

    /**
     * PHP Validation
     *
     * @param string $value The field value to be evaluated
     * @param string $isIn The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not.
     * @return mixed
     * @SuppressWarnings("unused")
     */
    public function evaluateFieldValue($value, $isIn, &$set)
    {
        return $value;
    }

    /**
     * Adds a flash message
     *
     * @param string $message
     * @param string $title optional message title
     * @param int $severity optional severity code
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function addFlashMessage($message, $title = '', $severity = FlashMessage::OK)
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException(
                'The flash message must be string, ' . gettype($message) . ' given.',
                1540981033
            );
        }

        /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->addMessage($message);
    }

    /**
     * Returns the translation of current language, stored in locallang_backend.xlf.
     *
     * @param string $key key in locallang_backend.xlf to translate
     * @param array $arguments optional arguments
     * @return string Translated text
     */
    protected function translate($key, array $arguments = [])
    {
        return LocalizationUtility::translate(
            'LLL:EXT:pti/Resources/Private/Language/locallang_backend.xlf:' . $key,
            'Pti',
            $arguments
        );
    }
}
