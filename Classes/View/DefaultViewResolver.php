<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class DefaultViewResolver implements ViewResolver
{
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function getViewForContentObject(?array $dbRow = [], ?string $template = ''): ViewInterface
    {
        return $this->getDefaultView();
    }

    public function getViewForExtbaseAction(ControllerContext $controllerContext, ?string $template): ViewInterface
    {
        return $this->getDefaultView();
    }

    protected function getDefaultView(): ViewInterface
    {
        $class = $this->extensionConfiguration->get('pti', 'defaultView');
        /** @var ViewInterface $view */
        $view = GeneralUtility::makeInstance($class);
        return $view;
    }
}
