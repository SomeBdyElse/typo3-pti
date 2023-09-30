<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DefaultViewResolver implements ViewResolverInterface
{
    protected ExtensionConfiguration $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function getViewForContentObject(
        ?array $dbRow = [],
        ?string $template = ''
    ): PtiViewInterface {
        if (isset($template) && $template === 'json') {
            return $this->getJsonView();
        }
        return $this->getDefaultView($template);
    }

    public function getViewForExtbaseAction(
        string $controllerObjectName,
        string $actionName,
        string $format,
        ?string $template
    ): PtiViewInterface {
        if (isset($format) && $format === 'json') {
            return $this->getJsonView();
        }
        return $this->getDefaultView($template);
    }

    protected function getDefaultView(?string $template): PtiViewInterface
    {
        $class = $this->extensionConfiguration->get('pti', 'defaultView');
        $view = GeneralUtility::makeInstance($class);

        if ($view instanceof TemplateBasedViewInterface) {
            $view->setTemplate($template);
        }

        return $view;
    }

    protected function getJsonView()
    {
        return GeneralUtility::makeInstance(JsonView::class);
    }
}
