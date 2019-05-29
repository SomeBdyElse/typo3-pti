<?php
namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use Itools\IAA\View\TwigView;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PtiContentObject extends AbstractContentObject
{
    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var TwigView
     */
    protected $view;

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


    public function __construct(ContentObjectRenderer $contentObjectRenderer)
    {
        parent::__construct($contentObjectRenderer);

        $this->view = GeneralUtility::makeInstance(TwigView::class);
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
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

        $this->view->setVariables($data);

        $templateName = $this->getTemplateName();
        if (!empty($templateName)) {
            $this->view->setTemplate($templateName);
        }

        return $this->view->render();
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
