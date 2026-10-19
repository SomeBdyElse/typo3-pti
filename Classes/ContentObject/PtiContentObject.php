<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class PtiContentObject extends AbstractContentObject
{
    protected array $conf;

    protected string $templateName;

    public function __construct(
        protected TypoScriptService $typoScriptService,
        protected ProcessorRunner $processorRunner,
        protected ViewResolverInterface $viewResolver,
        protected NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @param array $conf
     * @return string
     */
    public function render($conf = [])
    {
        $this->conf = $this->typoScriptService->convertTypoScriptArrayToPlainArray($conf);

        if (isset($this->conf['templateName'])) {
            $this->templateName = $this->cObj->stdWrapValue('templateName', $conf ?? []);
        }

        $data = $this->processorRunner->processData($this->cObj, $this->conf, $this->cObj->getCurrentTable());
        if (!isset($data)) {
            return '';
        }
        if (is_string($data)) {
            return $data;
        }

        $templateName = $this->getTemplateName();
        $view = $this->viewResolver->getViewForContentObject(is_array($data) ? $data : $this->cObj->data, $templateName);
        $view->setVariables($this->normalizeVariables($data));

        $this->lastChanged();

        return $view->render();
    }

    protected function getTemplateName()
    {
        return $this->templateName;
    }

    protected function normalizeVariables(object|array $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ((bool)($this->conf['normalize'] ?? null)) {
            $variables = $this->normalizer->normalize($data);
            if (!is_array($variables)) {
                throw new \UnexpectedValueException('PTI normalization must produce an array of template variables.');
            }
            return $variables;
        }

        return [$this->conf['as'] ?? 'data' => $data];
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
