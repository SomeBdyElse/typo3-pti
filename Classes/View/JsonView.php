<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

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
}
