<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsRecordProcessor
{
    public function __construct(
        public string $mainType,
        public string $recordType,
    ) {
    }
}
