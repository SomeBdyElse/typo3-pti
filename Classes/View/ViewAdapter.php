<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\View\ViewInterface;

class ViewAdapter implements ViewInterface
{
    protected ?array $settings;

    protected array $variables = [];

    /**
     * @param PtiDataProcessor[] $dataProcessors
     */
    public function __construct(
        protected array $dataProcessors,
        protected PtiViewInterface $view,
    ) {
    }

    public function render(string $templateFileName = ''): string
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

    public function assign(string $key, mixed $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function assignMultiple(array $values): self
    {
        $this->variables = array_replace_recursive($this->variables, $values);
        return $this;
    }
}
