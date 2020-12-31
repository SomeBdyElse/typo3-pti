<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

abstract class ExtbaseViewAdapter extends AbstractView
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $template = null;

    /**
     * @var Dispatcher
     */
    protected $signalDispatcher;

    public function injectDispatcher(Dispatcher $signalDispatcher)
    {
        $this->signalDispatcher = $signalDispatcher;
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
            $this->template
        );

        if ($view instanceof TemplateBasedView) {
            $view->setTemplate($this->getTemplate());
        }

        $variables = $this->convertVariables($this->variables);
        list($variables) = $this->signalDispatcher->dispatch(__CLASS__, 'beforeRendering', [ $variables ]);
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
