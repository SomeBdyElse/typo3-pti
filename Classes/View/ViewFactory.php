<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;

class ViewFactory implements ViewFactoryInterface
{
    protected array $overrides;

    public function __construct(
        protected ViewFactoryInterface $defaultViewFactory,
        protected ViewResolverInterface $viewResolver,
        protected ExtbaseProcessorRegistry $extbaseProcessorRegistry,
    ) {
    }

    public function create(ViewFactoryData $data): ViewInterface
    {
        $override = $this->checkOverride($data);
        if (isset($override)) {
            return $override;
        }

        return $this->defaultViewFactory->create($data);
    }

    protected function checkOverride(ViewFactoryData $data): ?ViewInterface
    {
        $extbaseRequestParameters = $data->request?->getAttribute('extbase');
        if (!isset($extbaseRequestParameters)) {
            return null;
        }

        $override = $this->extbaseProcessorRegistry->getProcessorForControllerAndAction(
            $extbaseRequestParameters->getControllerObjectName(),
            $extbaseRequestParameters->getControllerActionName(),
        );
        if (isset($override)) {
            $processors = array_map(
                fn (string $className): PtiDataProcessor => GeneralUtility::makeInstance($className),
                $override['processors'],
            );

            $view = $this->viewResolver->getViewForExtbaseAction(
                $data->request,
                $override['template'] ?? null,
            );

            $adapterClassName = $override['adapterClassName'] ?? ViewAdapter::class;
            $viewAdapter = GeneralUtility::makeInstance($adapterClassName, $processors, $view);
            $viewAdapter->assign('request', $data->request);
            return $viewAdapter;
        }

        return $this->defaultViewFactory->create($data);
    }
}
