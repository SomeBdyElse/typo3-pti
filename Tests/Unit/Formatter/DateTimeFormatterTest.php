<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Formatter;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PrototypeIntegration\PrototypeIntegration\Formatter\DateTimeFormatter;

class DateTimeFormatterTest extends UnitTestCase
{
    public function provideTestDataWithType(): array
    {
        return [
            [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM, 'en-US', 'Oct 20, 2022, 10:00:00 AM'],
            [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM, 'de-DE', '20.10.2022, 10:00:00'],
            [\IntlDateFormatter::LONG, \IntlDateFormatter::NONE, 'de-DE', '20. Oktober 2022'],
        ];
    }

    /** @dataProvider provideTestDataWithType */
    public function testFormat(int $dateType, int $timeType, ?string $locale, string $expected): void
    {
        $dateTimeFormatter = new DateTimeFormatter();
        $result = $dateTimeFormatter->format(
            new \DateTime('2022-10-20T10:00:00+00:00'),
            $dateType,
            $timeType,
            $locale
        );
        self::assertEquals($expected, $result);
    }

    public function provideTestDataWithPattern(): array
    {
        return [
            ['yyyy-MM-dd', 'en-US', '2022-10-20'],
            ['MM/dd/yyyy', 'en-US', '10/20/2022'],
            ['dd MMMM yyyy', 'en-US', '20 October 2022'],
            ['yyyy-MM-dd HH:mm', 'de-DE', '2022-10-20 10:00'],
            ['dd. MMMM yyyy', 'de-DE', '20. Oktober 2022'],
        ];
    }

    /** @dataProvider provideTestDataWithPattern */
    public function testFormatWithPattern(string $pattern, ?string $locale, string $expected): void
    {
        $dateTimeFormatter = new DateTimeFormatter();
        $result = $dateTimeFormatter->formatWithPattern(
            new \DateTime('2022-10-20T10:00:00+00:00'),
            $pattern,
            $locale,
        );
        self::assertEquals($expected, $result);
    }
}
