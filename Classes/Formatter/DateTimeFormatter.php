<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Formatter;

use IntlDateFormatter;

/**
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
     * @param string|null $locale
     * The locale e.G. "en_US" to use. Will default to the current php locale.
     *
     * @param string|null $pattern
     * The pattern allows a custom format. @link http://php.net/manual/en/intl.intldateformatter-constants.php
     *
     * @return string The formatted string or, if an error occurred, null
     */
    public function format(
        $value,
        int $dateType = IntlDateFormatter::MEDIUM,
        int $timeType = IntlDateFormatter::MEDIUM,
        ?string $locale = null,
        ?string $pattern = null
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
            $timeType,
            null,
            null,
            $pattern
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
     * @param string|null $locale @see DateTimeFormatter::format
     * @return string The formatted string or, if an error occurred, null
     */
    public function formatDate(
        $value,
        int $dateType = IntlDateFormatter::MEDIUM,
        ?string $locale = null
    ): ?string {
        return $this->format($value, $dateType, IntlDateFormatter::NONE, $locale);
    }

    /**
     * Convenience method to format the date and/or the time of a given value with custom defined pattern
     *
     * @param $value @see DateTimeFormatter::format
     * @param string|null $locale @see DateTimeFormatter::format
     * @param string|null $pattern @see DateTimeFormatter::format
     * @return null|string The formatted string or, if an error occurred, null
     */
    public function formatWithPattern(
        $value,
        ?string $locale = null,
        ?string $pattern = null
    ): ?string {
        return $this->format($value, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $locale, $pattern);
    }
}
