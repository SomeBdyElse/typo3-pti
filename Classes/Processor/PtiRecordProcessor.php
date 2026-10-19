<?php

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\RecordInterface;

interface PtiRecordProcessor
{
    public function __invoke(
        ServerRequestInterface $request,
        array $configuration,
        RecordInterface $record,
    ): object|array|string|null;
}
