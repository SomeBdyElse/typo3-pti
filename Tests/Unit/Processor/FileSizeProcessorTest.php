<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\FileSizeProcessor;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FileSizeProcessorTest extends UnitTestCase
{
    /**
     * @var FileSizeProcessor
     */
    protected $fileSizeProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSizeProcessor = new FileSizeProcessor();
    }

    /**
     * @param mixed $fileSize
     * @param array $formatSettings
     * @param string $expected
     * @test
     * @dataProvider defaultFormatDataProvider
     */
    public function defaultFormatFileSize($fileSize, $formatSettings, $expected)
    {
        self::assertEquals($expected, $this->fileSizeProcessor->formatFileSize($fileSize, $formatSettings));
    }

    /**
     * @return array
     */
    public static function defaultFormatDataProvider(): array
    {
        return [
            [
                'fileSize' => 'invalid',
                'formatSettings' => [],
                'expected' => '0 B',
            ],
            [
                'fileSize' => '',
                'formatSettings' => [],
                'expected' => '0 B',
            ],
            [
                'fileSize' => [],
                'formatSettings' => [],
                'expected' => '0 B',
            ],
            [
                'fileSize' => 123,
                'formatSettings' => [],
                'expected' => '123 B',
            ],
            [
                'fileSize' => '43008',
                'formatSettings' => [
                    'decimals' => 1,
                ],
                'expected' => '42.0 KB',
            ],
            [
                'fileSize' => '1024',
                'formatSettings' => [
                    'decimals' => 1,
                ],
                'expected' => '1.0 KB',
            ],
            [
                'fileSize' => '1022',
                'formatSettings' => [
                    'decimals' => 2,
                ],
                'expected' => '1022.00 B',
            ],
            [
                'fileSize' => '1022',
                'formatSettings' => [
                    'decimals' => 2,
                    'thousandsSeparator' => ',',
                ],
                'expected' => '1,022.00 B',
            ],
            [
                'fileSize' => 1073741823,
                'formatSettings' => [
                    'decimals' => 1,
                    'decimalSeparator' => ',',
                    'thousandsSeparator' => '.',
                ],
                'expected' => '1.024,0 MB',
            ],
            [
                'fileSize' => pow(1024, 5),
                'formatSettings' => [
                    'decimals' => 1,
                ],
                'expected' => '1.0 PB',
            ],
            [
                'fileSize' => pow(1024, 8),
                'formatSettings' => [
                    'decimals' => 1,
                ],
                'expected' => '1.0 YB',
            ],
        ];
    }
}
