<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DefaultViewResolver implements ViewResolver
{
    protected ExtensionConfiguration $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function getViewForContentObject(?array $dbRow = [], ?string $template = ''): PtiViewInterface
    {
        return $this->getDefaultView();
    }

    public function getViewForExtbaseAction(
        string $controllerObjectName,
        string $actionName,
        string $format,
        ?string $template
    ): PtiViewInterface {
        return $this->getDefaultView();
    }

    protected function getDefaultView(): PtiViewInterface
    {
        $class = $this->extensionConfiguration->get('pti', 'defaultView');
        $view = GeneralUtility::makeInstance($class);
        return $view;
    }
}
