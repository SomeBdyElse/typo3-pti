<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

interface TemplateBasedView
{
    public function setTemplate(string $templateIdentifier);

    public function getTemplate(): string;
}
