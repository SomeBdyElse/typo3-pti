<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use Psr\Http\Message\ServerRequestInterface;

class ContentAreasProcessor
{
    public function __construct(
        protected ContentElementProcessor $recordProcessor,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        array $configuration,
        array $contentAreas,
    ): array {
        $resultAreas = [];
        foreach ($contentAreas as $columnName => $contentArea) {
            $resultElements = [];
            foreach ($contentArea['records'] as $record) {
                $result = $this->recordProcessor->processRecord(
                    $request,
                    $configuration,
                    $record,
                );
                if (isset($result)) {
                    $resultElements[] = $result;
                }
            }
            $resultAreas[$columnName] = $resultElements;
        }

        return $resultAreas;
    }
}
