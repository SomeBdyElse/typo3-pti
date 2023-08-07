<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\View\Event\ExtbaseViewAdapterVariablesConvertedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\View\AbstractView;

abstract class ExtbaseViewAdapter extends AbstractView implements ViewAdapterContextAware
{
    protected ?array $settings;

    protected ?string $template = null;

    protected EventDispatcher $eventDispatcher;

    protected ExtbaseViewAdapterContext $viewAdapterContext;

    public function injectDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setViewAdapterContext(ExtbaseViewAdapterContext $viewAdapterContext): void
    {
        $this->viewAdapterContext = $viewAdapterContext;
    }

    /**
     * Renders the view
     *
     * @return string The rendered view
     * @api
     */
    public function render()
    {
        $viewResolver = GeneralUtility::makeInstance(ViewResolverInterface::class);

        $view = $viewResolver->getViewForExtbaseAction(
            $this->viewAdapterContext->getControllerObjectName(),
            $this->viewAdapterContext->getActionName(),
            $this->viewAdapterContext->getFormat(),
            $this->getTemplate()
        );

        if ($view instanceof TemplateBasedViewInterface) {
            $view->setTemplate($this->getTemplate());
        }

        $variables = $this->convertVariables($this->variables);
        $variables = $this->eventDispatcher->dispatch(new ExtbaseViewAdapterVariablesConvertedEvent($variables))->getVariables();
        $view->setVariables($variables);

        return $view->render();
    }

    protected function convertVariables(array $variables): array
    {
        return $variables;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function injectSettings(?array $settings): void
    {
        $this->settings = $settings;
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('', 1691402012205);
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('', 1691402017852);
    }
}
