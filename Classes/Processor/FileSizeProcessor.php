<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileSizeProcessor
{
    protected static array $defaultConfiguration = [
        'decimals' => 0,
        'units' => 'B,KB,MB,GB,TB,PB,EB,ZB,YB',
    ];

    /**
     * @param float|int $size
     * @param array $configuration
     * @return string
     */
    public function formatFileSize($size, $configuration = []): string
    {
        $configuration = array_replace($this->getDefaultConfiguration(), $configuration);

        $units = GeneralUtility::trimExplode(',', $configuration['units']);

        if (is_numeric($size)) {
            $size = (float)$size;
        }

        if (!is_int($size) && !is_float($size)) {
            $size = 0;
        }

        $bytes = max($size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(2, 10 * $pow);

        return sprintf(
            '%s %s',
            number_format(
                round($bytes, 4 * $configuration['decimals']),
                $configuration['decimals'],
                $configuration['decimalSeparator'],
                $configuration['thousandsSeparator']
            ),
            $units[$pow]
        );
    }

    /**
     * @return array
     */
    protected function getDefaultConfiguration(): array
    {
        $locale = localeconv();
        return array_replace(self::$defaultConfiguration, [
            'decimalSeparator' => $locale['decimal_point'],
            'thousandsSeparator' => $locale['thousands_sep'],
        ]);
    }
}
