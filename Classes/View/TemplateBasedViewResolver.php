<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class TemplateBasedViewResolver extends DefaultViewResolver
{
    public function getViewForContentObject(?array $dbRow = [], ?string $template = ''): PtiViewInterface
    {
        if ($template == 'json') {
            return GeneralUtility::makeInstance(JsonView::class);
        }
        return parent::getViewForContentObject($dbRow, $template);
    }

    public function getViewForExtbaseAction(
        string $controllerObjectName,
        string $actionName,
        string $format,
        ?string $template
    ): PtiViewInterface {
        if ($template == 'json') {
            return GeneralUtility::makeInstance(JsonView::class);
        }
        return parent::getViewForExtbaseAction(
            $controllerObjectName,
            $actionName,
            $format,
            $template
        );
    }
}
