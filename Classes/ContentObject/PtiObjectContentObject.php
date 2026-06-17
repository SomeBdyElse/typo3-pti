<?php

namespace PrototypeIntegration\PrototypeIntegration\ContentObject;

use PrototypeIntegration\PrototypeIntegration\Serialization\SerializerFactory;
use PrototypeIntegration\PrototypeIntegration\View\ViewResolverInterface;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;

class PtiObjectContentObject extends AbstractContentObject
{
    public function __construct(
        protected ViewResolverInterface $viewResolver,
        protected SerializerFactory $serializerFactory,
        protected RecordFactory $recordFactory,
    ) {

    }
    public function render($conf = [])
    {
        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow(
            $this->cObj->getCurrentTable(),
            $this->cObj->data,
        );
        $controller = GeneralUtility::makeInstance($conf['controller']);
        $object = $controller->__invoke(
            $this->request,
            $this->cObj,
            $record,
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
