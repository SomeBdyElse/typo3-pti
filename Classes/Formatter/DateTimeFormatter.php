<?php
namespace PrototypeIntegration\PrototypeIntegration\Formatter;

use IntlDateFormatter;

/**
 * Class DateTimeFormatter
 * @package PrototypeIntegration\PrototypeIntegration\Processor
 *
 * Wrapper class for PHP's IntlDateFormatter
 */
class DateTimeFormatter
{
    /**
     * Format the given date and time according to the given format and locale.
     * This is a wrapper around the IntlDateFormatter::format method.
     *
     * @param $value
     * The value to format. @see IntlDateFormatter::format() for argument types
     *
     * @param int $dateType
     * The date format to use. @link http://php.net/manual/en/intl.intldateformatter-constants.php
     *
     * @param int $timeType
     * The time format to use @link http://php.net/manual/en/intl.intldateformatter-constants.php
     *
     * @param null|string $locale
     * The locale e.G. "en_US" to use. Will default to the current php locale.
     *
     * @return string The formatted string or, if an error occurred, null
     */
    public function format(
        $value,
        int $dateType = IntlDateFormatter::MEDIUM,
        int $timeType = IntlDateFormatter::MEDIUM,
        ?string $locale = null
    ): ?string {
        if (is_null($locale)) {
            // get current locale
            $currentLocale = setlocale(LC_TIME, 0);
            if (is_string($currentLocale)) {
                $locale = explode('.', $currentLocale, 2)[0];
            }
        }

        $dateFormatter = new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType
        );

        $result = $dateFormatter->format($value);
        if (! is_string($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Convenience method to format just the date (not the time) of a given value
     *
     * @param $value @see DateTimeFormatter::format
     * @param int $dateType @see DateTimeFormatter::format
     * @param null|string $locale @see DateTimeFormatter::format
     * @return string The formatted string or, if an error occurred, null
     */
    public function formatDate(
        $value,
        int $dateType = IntlDateFormatter::MEDIUM,
        ?string $locale = null
    ): ?string {
        return $this->format($value, $dateType, IntlDateFormatter::NONE, $locale);
    }
}
