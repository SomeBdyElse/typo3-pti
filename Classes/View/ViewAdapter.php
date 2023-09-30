<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;
use TYPO3Fluid\Fluid\View\AbstractView;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewAdapter extends AbstractView implements ViewInterface
{
    protected ?array $settings;

    /**
     * @param PtiDataProcessor[] $dataProcessors
     */
    public function __construct(
        protected array $dataProcessors,
        protected PtiViewInterface $view,
    ) {
    }

    /**
     * @return string The rendered view
     */
    public function render()
    {
        $variables = $this->variables;
        foreach ($this->dataProcessors as $dataProcessor) {
            $variables = $dataProcessor->process(
                $variables,
                $variables['settings'],
            );
        }

        $this->view->setVariables($variables);
        return $this->view->render();
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('', 1691406217099);
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('', 1691406221493);
    }
}
