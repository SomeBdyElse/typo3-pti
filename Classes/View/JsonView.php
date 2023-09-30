<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Utility\Exception\NotImplementedMethodException;

class JsonView implements PtiViewInterface
{
    protected array $variables = [];

    public function render(): string
    {
        return json_encode($this->variables, JSON_PRETTY_PRINT);
    }

    public function setVariables($variables): void
    {
        $this->variables = $variables;
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('renderSection not implemented for JsonView', 1690795638213);
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false)
    {
        throw new NotImplementedMethodException('renderSection not implemented for JsonView', 1690795646994);
    }
}
