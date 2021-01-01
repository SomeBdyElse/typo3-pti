<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\View\Event\ExtbaseViewAdapterVariablesConvertedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;

abstract class ExtbaseViewAdapter extends AbstractView
{
    protected ?array $settings;

    protected ?string $template = null;

    protected EventDispatcher $eventDispatcher;

    public function injectDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Renders the view
     *
     * @return string The rendered view
     * @api
     */
    public function render()
    {
        $viewResolverClass = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['view']['viewResolver'];
        /** @var ViewResolver $viewResolver */
        $viewResolver = GeneralUtility::makeInstance($viewResolverClass);

        $view = $viewResolver->getViewForExtbaseAction(
            $this->controllerContext,
            $this->getTemplate()
        );

        if ($view instanceof TemplateBasedView) {
            $view->setTemplate($this->getTemplate());
        }

        $variables = $this->convertVariables($this->variables);
        $variables = $this->eventDispatcher->dispatch(new ExtbaseViewAdapterVariablesConvertedEvent($variables))->getVariables();
        $view->assignMultiple($variables);

        return $view->render();
    }

    protected function convertVariables(array $variables): array
    {
        return $variables;
    }

    public function setControllerContext(ControllerContext $controllerContext)
    {
        parent::setControllerContext($controllerContext);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param array $settings
     */
    public function injectSettings(?array $settings): void
    {
        $this->settings = $settings;
    }
}
