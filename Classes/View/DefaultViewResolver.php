<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class DefaultViewResolver implements ViewResolver
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    public function __construct(ObjectManager $objectManager, ExtensionConfiguration $extensionConfiguration)
    {
        $this->objectManager = $objectManager;
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
        $view = $this->objectManager->get($class);
        return $view;
    }
}
