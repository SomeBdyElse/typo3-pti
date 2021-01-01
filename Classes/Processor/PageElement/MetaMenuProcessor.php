<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

class MetaMenuProcessor
{
    public function process(array $typo3MenuData, array $metaNavIcon = []): array
    {
        $twigMenuData = $typo3MenuData;

        foreach ($twigMenuData as $key => $menuItem) {
            $twigMenuData[$key] = $this->addPageInformation($menuItem, $metaNavIcon);
        }

        return $twigMenuData;
    }

    protected function addPageInformation(array $menuData, array $metaNavIcon = []): array
    {
        $menuData['doktype'] = $menuData['data']['doktype'];
        $menuData['icon'] = '';

        $iconKey = array_search($menuData['data']['uid'], $metaNavIcon);
        if (!empty($metaNavIcon) && $iconKey !== false) {
            $menuData['icon'] = $iconKey;
        }

        return $menuData;
    }
}
