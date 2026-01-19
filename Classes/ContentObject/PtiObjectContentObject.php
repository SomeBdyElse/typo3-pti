<?php

namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use Bfm\Bfm\Processors\Page\PageProcessor;
use PrototypeIntegration\PrototypeIntegration\Serialization\SerializerFactory;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolverInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class PtiObjectContentObject extends AbstractContentObject
{
    public function __construct(
        protected ViewResolverInterface $viewResolver,
        protected SerializerFactory $serializerFactory,
    ) {

    }
    public function render($conf = [])
    {
        $controller = GeneralUtility::makeInstance($conf['controller']);
        $object = $controller->__invoke(
            $this->request,
            $this->cObj,
            $this->cObj->data,
            $conf
        );

        $serializer = $this->serializerFactory->getSerializer();
        $pageData = $serializer->normalize($object);

        $view = $this->viewResolver->getViewForContentObject(
            $this->cObj->data,
            $conf['templateName'] ?? '',
        );

        $view->setVariables($pageData);

        return $view->render();
    }
}