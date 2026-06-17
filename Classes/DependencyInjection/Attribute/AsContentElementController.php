<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsContentElementController
{
    public function __construct(
        public string $CType,
    ) {
    }
}
