<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Extbase\Mvc\RequestInterface;

interface ViewResolverInterface
{
    public function getViewForContentObject(
        ?array $dbRow = [],
        ?string $template = ''
    ): PtiViewInterface;

    public function getViewForExtbaseAction(
        RequestInterface $extbaseRequest,
        ?string $template = null,
    ): PtiViewInterface;
}
