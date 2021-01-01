<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View\Event;

class ExtbaseViewAdapterVariablesConvertedEvent
{
    protected array $variables;

    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
