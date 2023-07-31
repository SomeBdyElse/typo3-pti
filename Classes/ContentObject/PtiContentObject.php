<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedViewInterface;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolver;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class PtiContentObject extends AbstractContentObject
{
    protected ViewResolver $viewResolver;

    protected array $conf;

    protected string $templateName;

    public function __construct(
        protected TypoScriptService $typoScriptService,
        protected ProcessorRunner $processorRunner,
    ) {
        $viewResolverClass = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['view']['viewResolver'];
        $this->viewResolver = GeneralUtility::makeInstance($viewResolverClass);
    }

    /**
     * @param array $conf
     * @return string
     */
    public function render($conf = [])
    {
        $this->conf = $this->typoScriptService->convertTypoScriptArrayToPlainArray($conf);

        if (isset($this->conf['templateName'])) {
            $this->templateName = $this->conf['templateName'];
        }

        $data = $this->processorRunner->processData($this->cObj, $this->conf, $this->cObj->getCurrentTable());
        if (!isset($data)) {
            return '';
        }

        $templateName = $this->getTemplateName();
        $view = $this->viewResolver->getViewForContentObject($data, $templateName);
        $view->setVariables($data);

        if ($view instanceof TemplateBasedViewInterface) {
            $view->setTemplate($templateName);
        }

        $this->lastChanged();

        return $view->render();
    }

    protected function getTemplateName()
    {
        return $this->templateName;
    }

    protected function lastChanged(): void
    {
        $contentObjectRenderer = $this->getContentObjectRenderer();
        $table = $contentObjectRenderer->getCurrentTable();
        $timestampColumn = $GLOBALS['TCA'][$table]['ctrl']['tstamp'] ?? 'tstamp';
        if (isset($contentObjectRenderer->data[$timestampColumn])) {
            $contentObjectRenderer->lastChanged($contentObjectRenderer->data[$timestampColumn]);
        }
    }
}
