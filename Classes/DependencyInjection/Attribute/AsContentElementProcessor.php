<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsContentElementProcessor
{
    public function __construct(
        public string $CType,
    ) {
    }
}
