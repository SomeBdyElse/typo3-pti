<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

interface TemplateBasedViewInterface
{
    public function setTemplate(string $templateIdentifier);

    public function getTemplate(): string;
}
