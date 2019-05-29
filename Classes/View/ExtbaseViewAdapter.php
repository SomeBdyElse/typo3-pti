<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use Itools\IAA\View\TwigView;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class ExtbaseViewAdapter extends AbstractView
{
    /**
     * @var TwigView
     */
    protected $renderer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LocalizationUtility
     */
    protected $localizationUtility;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $template = null;

    public function __construct(
        TwigView $renderer,
        ObjectManager $objectManager,
        LocalizationUtility $localizationUtility
    ) {
        $this->renderer = $renderer;
        $this->objectManager = $objectManager;
        $this->localizationUtility = $localizationUtility;
    }

    /**
     * Renders the view
     *
     * @return string The rendered view
     * @api
     */
    public function render()
    {
        $this->renderer->setTemplate($this->getTemplate());
        $this->renderer->setVariables(
            $this->convertVariables($this->variables)
        );
        return $this->renderer->render();
    }

    protected function convertVariables(array $variables): array
    {
        return $variables;
    }

    public function setControllerContext(ControllerContext $controllerContext)
    {
        parent::setControllerContext($controllerContext);
        $this->renderer->setControllerContext($controllerContext);
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
