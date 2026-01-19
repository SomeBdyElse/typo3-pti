<?php

namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\View\ViewResolverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class PtiObjectContentObject extends AbstractContentObject
{
    public function __construct(
        protected ViewResolverInterface $viewResolver,
        protected NormalizerInterface $normalizer,
    ) {
    }
    public function render($conf = [])
    {
        $processor = isset($conf['processor'])
            ? GeneralUtility::makeInstance($conf['processor'])
            : null;
        if (!isset($processor)) {
            return '';
        }

        $result = $processor->__invoke(
            $this->request,
            $conf,
            $this->cObj->data,
        );
        if ($result === null) {
            return '';
        }

        $view = $this->viewResolver->getViewForContentObject(
            $this->cObj->data,
            $conf['templateName'] ?? '',
        );

        $variables = $this->normalizeVariables($conf, $result);
        $view->setVariables($variables);

        return $view->render();
    }

    protected function normalizeVariables(array $conf, object|array $result): array
    {
        if (is_array($result)) {
            return $result;
        }

        if ((bool)($conf['normalize'] ?? null)) {
            $variables = $this->normalizer->normalize($result);
            if (!is_array($variables)) {
                throw new \UnexpectedValueException('PTI_OBJECT normalization must produce an array of template variables.');
            }
            return $variables;
        }

        return [$conf['as'] ?? 'data' => $result];
    }
}
