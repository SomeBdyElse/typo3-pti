<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class TemplateBasedViewResolver extends DefaultViewResolver
{
    public function getViewForContentObject(?array $dbRow = [], ?string $template = ''): ViewInterface
    {
        if ($template == 'json') {
            return GeneralUtility::makeInstance(JsonView::class);
        }
        return parent::getViewForContentObject($dbRow, $template);
    }

    public function getViewForExtbaseAction(ControllerContext $controllerContext, ?string $template): ViewInterface
    {
        if ($template == 'json') {
            return GeneralUtility::makeInstance(JsonView::class);
        }
        return parent::getViewForExtbaseAction($controllerContext, $template);
    }
}
