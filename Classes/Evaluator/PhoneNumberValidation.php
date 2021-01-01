<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Evaluator;

use TYPO3\CMS\Core\Messaging\FlashMessage;

class PhoneNumberValidation extends AbstractFieldValidator
{
    /**
     * JavaScript validation
     *
     * @return string javascript function code for js validation
     */
    public function returnFieldJs()
    {
        return '
            if (value.substring(0, 2) == "00") {
                value = "+" + value.substring(2);
            }
        
            return value;
            ';
    }

    /**
     * Server-side validation/evaluation on saving the record
     *
     * @param string $value The field value to be evaluated
     * @param string $isIn The "is_in" value of the field configuration from TCA
     * @param bool $set Boolean defining if the value is written to the database or not.
     * @return string Evaluated field value
     * @SuppressWarnings("unused")
     */
    public function evaluateFieldValue($value, $isIn, &$set): string
    {
        $rfcPhoneNumber = '';
        $phoneNumber = $value;

        if (empty($phoneNumber)) {
            $set = true;
            return $phoneNumber;
        }

        if (substr($phoneNumber, 0, 1) == '+') {
            $rfcPhoneNumber = $phoneNumber;
        } elseif (substr($phoneNumber, 0, 2) == '00') {
            $rfcPhoneNumber = '+' . substr($phoneNumber, 2);
        }

        if (empty($rfcPhoneNumber)) {
            $set = false;
            $this->addFlashMessage(
                $this->translate('formeval_phoneNumber', [$value]),
                $this->translate('formeval_headline', [$value]),
                FlashMessage::ERROR
            );

            return $value;
        }

        $rfcPhoneNumber = preg_replace('/[^0-9 +]/', '', $rfcPhoneNumber);
        $set = true;
        return $rfcPhoneNumber;
    }
}
