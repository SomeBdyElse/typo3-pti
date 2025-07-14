<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3Fluid\Fluid\View\AbstractTemplateView;

readonly class FluidViewAdapter extends \TYPO3\CMS\Fluid\View\FluidViewAdapter
{
    public function __construct(
        protected array $dataProcessors,
        protected PtiViewInterface $ptiView,
    ) {
        parent::__construct(new class () extends AbstractTemplateView {});
    }

    #[\Override]
    public function render($templateFileName = null): string
    {
        $renderingContext = $this->getRenderingContext();
        $variableProvider = $renderingContext->getVariableProvider();
        $variables = $variableProvider->getAll();
        $variables['renderingContext'] = $renderingContext;
        foreach ($this->dataProcessors as $dataProcessor) {
            $variables = $dataProcessor->process(
                $variables,
                $variables['settings']
            );
        }

        $this->ptiView->setVariables($variables);
        return $this->ptiView->render();
    }
}
