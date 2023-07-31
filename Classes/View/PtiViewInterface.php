<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

interface PtiViewInterface
{
    public function render(): string;

    public function setVariables(array $variables): void;
}
