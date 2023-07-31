<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

interface ViewResolver
{
    public function getViewForContentObject(
        ?array $dbRow = [],
        ?string $template = ''
    ): PtiViewInterface;

    public function getViewForExtbaseAction(
        string $controllerObjectName,
        string $actionName,
        string $format,
        ?string $template
    ): PtiViewInterface;
}
