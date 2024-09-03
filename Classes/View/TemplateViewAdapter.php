<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\View\ViewInterface;

class TemplateViewAdapter extends TemplateView implements ViewInterface
{
    public function __construct(
        protected array $dataProcessors,
        protected PtiViewInterface $view
    ) {
        parent::__construct(null);
    }

    #[\Override]
    public function render($actionName = null): string
    {
        $variables = $this->getRenderingContext()->getVariableProvider()->getAll();
        foreach ($this->dataProcessors as $dataProcessor) {
            $variables = $dataProcessor->process(
                $variables,
                $variables['settings']
            );
        }

        $this->view->setVariables($variables);
        return $this->view->render();
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false)
    {
        return '';
    }

    public function renderPartial($partialName, $sectionName = null, array $variables = [], $ignoreUnknown = false)
    {
        return '';
    }
}
