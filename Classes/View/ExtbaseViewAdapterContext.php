<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

class ExtbaseViewAdapterContext
{
    public function __construct(
        protected string $controllerObjectName,
        protected string $actionName,
        protected string $format
    ) {
    }

    public function getControllerObjectName(): string
    {
        return $this->controllerObjectName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
