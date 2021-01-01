<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

interface ViewResolver
{
    public function getViewForContentObject(?array $dbRow = [], ?string $template = ''): ViewInterface;

    public function getViewForExtbaseAction(ControllerContext $controllerContext, ?string $template): ViewInterface;
}
