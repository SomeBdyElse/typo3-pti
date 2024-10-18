<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

class ExtbaseProcessorRegistry
{
    public function __construct(
        protected array $overrides = [],
    ) {
    }

    public function addOverride(
        string $controller,
        string $action,
        string $processorClassName,
        ?string $template = null,
    ) {
        $this->overrides[$controller][$action] = [
            'processors' => [$processorClassName],
            'template' => $template,
        ];
    }

    public function getOverrides(): array
    {
        return $this->overrides;
    }

    /** array<processors: string[], template?: ?string> */
    public function getProcessorForControllerAndAction(string $controller, string $action): ?array
    {
        return $this->overrides[$controller][$action] ?? null;
    }
}
