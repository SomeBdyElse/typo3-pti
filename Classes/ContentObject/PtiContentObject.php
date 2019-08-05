<?php
namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\View\DefaultViewResolver;
use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedView;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolver;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var ViewResolver
     */
    protected $viewResolver;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    public function __construct(ContentObjectRenderer $contentObjectRenderer)
    {
        parent::__construct($contentObjectRenderer);

        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $viewResolverClass = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['view']['viewResolver'];
        $this->viewResolver = $this->objectManager->get($viewResolverClass);
        $this->signalSlotDispatcher = $this->objectManager->get(Dispatcher::class);
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

        $data = $this->cObj->data;
        if (isset($this->conf['dataProcessors'])) {
            foreach ($this->conf['dataProcessors'] as $dataProcessorConfiguration) {
                // Get classname
                $dataProcessorClassName = null;
                if (is_string($dataProcessorConfiguration)) {
                    $dataProcessorClassName = $dataProcessorConfiguration;
                    $dataProcessorConfiguration = [];
                }
                if (isset($dataProcessorConfiguration['_typoScriptNodeValue'])) {
                    $dataProcessorClassName = $dataProcessorConfiguration['_typoScriptNodeValue'];
                    unset($dataProcessorConfiguration['_typoScriptNodeValue']);
                }
                if (! isset($dataProcessorClassName)) {
                    throw new \RuntimeException('Missing className for dataProcessor', 1521297809618);
                }

                $data = $this->runDataProcessor($dataProcessorClassName, $dataProcessorConfiguration, $data);
                if (is_null($data)) {
                    return '';
                }
            }
        }

        if ($this->cObj->getCurrentTable() == 'tt_content') {
            $isTranslated = ! empty($this->cObj->data['_LOCALIZED_UID']);
            $data['uid'] =  $isTranslated ? $this->cObj->data['_LOCALIZED_UID'] : $this->cObj->data['uid'];
        }

        list($data) = $this->signalSlotDispatcher->dispatch(__CLASS__, 'beforeRendering', array($data));

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

    protected function runDataProcessor($className, $configuration, $data)
    {
        // Instantiate class
        $dataProcessor = $this->objectManager->get($className);

        if (! $dataProcessor instanceof PtiDataProcessor) {
            throw new \RuntimeException(
                'Class ' . get_class($dataProcessor) . ' must implement Interface ' . PtiDataProcessor::class,
                1542889749
            );
        }

        if (method_exists($dataProcessor, 'injectContentObjectRenderer')) {
            $dataProcessor->injectContentObjectRenderer($this->cObj);
        }

        $data = $dataProcessor->process($data, $configuration);
        return $data;
    }
}
