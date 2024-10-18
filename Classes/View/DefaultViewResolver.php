<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class DefaultViewResolver implements ViewResolverInterface
{
    protected ExtensionConfiguration $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function getViewForContentObject(
        ?array $dbRow = [],
        ?string $template = ''
    ): PtiViewInterface {
        if (isset($template) && $template === 'json') {
            return $this->getJsonView();
        }
        return $this->getDefaultView($template);
    }

    public function getViewForExtbaseAction(
        RequestInterface $extbaseRequest,
        ?string $template = null,
    ): PtiViewInterface {
        // Allow the CompoundProcessor to force json output
        $contentObject = $extbaseRequest->getAttribute('currentContentObject');

        $ptiForceView = $contentObject?->data['_pti_format'] ?? null;
        if (
            // USER_INT objects have to be rendered with their real template on the second pass, when replacing the USER_INT string
            $contentObject->getUserObjectType() === ContentObjectRenderer::OBJECTTYPE_USER
            && isset($ptiForceView)
        ) {
            $format = $ptiForceView;
        }

        $ptiForceViewUncached = $contentObject?->data['_pti_format_uncached'] ?? null;
        if (
            $contentObject->getUserObjectType() === ContentObjectRenderer::OBJECTTYPE_USER_INT
            && isset($ptiForceViewUncached)
        ) {
            $format = $ptiForceViewUncached;
        }

        if (isset($format) && $format === 'json') {
            return $this->getJsonView();
        }

        return $this->getDefaultView($template);
    }

    protected function getDefaultView(?string $template): PtiViewInterface
    {
        $class = $this->extensionConfiguration->get('pti', 'defaultView');
        $view = GeneralUtility::makeInstance($class);

        if ($view instanceof TemplateBasedViewInterface) {
            $view->setTemplate($template);
        }

        return $view;
    }

    protected function getJsonView()
    {
        return GeneralUtility::makeInstance(JsonView::class);
    }
}
