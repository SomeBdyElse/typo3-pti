<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

interface PtiDataProcessor
{
    public function process(array $data, array $configuration): ?array;
}
