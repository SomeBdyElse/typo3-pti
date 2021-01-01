<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class JsonView extends AbstractView implements ViewInterface
{
    public function render()
    {
        return json_encode($this->variables, JSON_PRETTY_PRINT);
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
    }
}