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
     * @return string The formatted string or, if an error occurred, null
     */
    public function format(
        $value,
        int $dateType = IntlDateFormatter::MEDIUM,
        int $timeType = IntlDateFormatter::MEDIUM,
        ?string $locale = null
    ): ?string {
        $dateFormatter = $this->getDateFormatter($locale, $dateType, $timeType);

        $result = $dateFormatter->format($value);

        return is_string($result) ? $result : null;
    }

    /**
     * Format the given date and time according to the given pattern and locale.
     * This is a wrapper around the IntlDateFormatter::format method.
     *
     * @param $value
     * The value to format. @see IntlDateFormatter::format() for argument types
     *
     * @param $pattern
     * The pattern to use to format the locale @see IntlDateFormatter::setPattern()
     *
     * @param string|null $locale
     * The locale e.G. "en_US" to use. Will default to the current php locale.
     *
     * @return string The formatted string or, if an error occurred, null
     */
    public function formatWithPattern(
        $value,
        string $pattern,
        ?string $locale = null
    ): ?string {
        $dateFormatter = $this->getDateFormatter($locale);
        $dateFormatter->setPattern($pattern);

        $result = $dateFormatter->format($value);

        return is_string($result) ? $result : null;
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

    protected function getDateFormatter(
        ?string $locale,
        int $dateType = IntlDateFormatter::MEDIUM,
        int $timeType = IntlDateFormatter::MEDIUM
    ): IntlDateFormatter {
        $locale = $locale ?? $this->getCurrentLocale();

        return new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType
        );
    }

    protected function getCurrentLocale(): ?string
    {
        $currentLocale = setlocale(LC_TIME, 0);
        return is_string($currentLocale) ? explode('.', $currentLocale, 2)[0] : null;
    }
}
