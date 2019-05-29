<?php
namespace PrototypeIntegration\PrototypeIntegration\Processor;

interface PtiDataProcessor
{
    /**
     *
     * @param array $data
     * @param array $configuration
     * @return null|array
     */
    public function process(array $data, array $configuration): ?array;
}
