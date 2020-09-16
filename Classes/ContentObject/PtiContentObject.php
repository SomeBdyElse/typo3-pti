<?php
namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedView;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolver;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PtiContentObject extends AbstractContentObject
{
    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var array
     */
    protected $conf;

    /**
     * @var ProcessorRunner
     */
    protected $processorRunner;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var ViewResolver
     */
    protected $viewResolver;

    public function __construct(ContentObjectRenderer $contentObjectRenderer)
    {
        parent::__construct($contentObjectRenderer);

        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->processorRunner = GeneralUtility::makeInstance(ProcessorRunner::class);

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

        $data = $this->processorRunner->processData($this->cObj->data, $this->conf, $this->cObj->getCurrentTable());
        $templateName = $this->getTemplateName();

        $view = $this->viewResolver->getViewForContentObject($data ?: [], $templateName);
        $view->setVariables($data);

        if ($view instanceof TemplateBasedView) {
            $view->setTemplate($templateName);
        }

        return $view->render();
    }

    protected function getTemplateName()
    {
        return $this->templateName;
    }
}
